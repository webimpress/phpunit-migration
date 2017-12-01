<?php

namespace Webimpress\PHPUnitMigration;

use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Generator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends Command
{
    private $composer;

    private $versions = [];

    private $versionsJson;

    protected function configure()
    {
        $this
            ->setDescription('')
            ->setHelp('')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path to the project to migrate PHPUnit',
                realpath(getcwd())
            );
    }
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $path = $input->getArgument('path');
        if (! is_dir($path)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid package path provided; directory "%s" does not exist',
                $path
            ));
        }
        if (! file_exists(sprintf('%s/composer.json', $path))) {
            throw new InvalidArgumentException(sprintf(
                'Cannot locate composer.json file in directory "%s"',
                $path
            ));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $phpunit = $this->getPHPUnitVersion()) {
            $output->writeln('<error>Cannot detect PHPUnit version in your composer.json</error>');
            return 1;
        }

        if (! $php = $this->getPHPVersion()) {
            $output->writeln('<error>Cannot detect PHP version in your composer.json</error>');
            return 1;
        }

        $minPHPUnitVersion = $this->findMinimumPHPUnitVersion($phpunit);
        $newPHPUnitVersions = $this->findPHPUnitVersion($php);

        $from = explode('.', $minPHPUnitVersion)[0];
        $to = explode('.', $newPHPUnitVersions[0])[0];

        foreach ($this->fileIterator() as $file) {
            $content = file_get_contents($file);
            if ($to >= 5) {
                $this->replaceTestCase($content);
            }

            file_put_contents($file, $content);
        }

        $composer = json_decode(file_get_contents('composer.json'), true);
        foreach ($composer['require-dev'] as $key => &$value) {
            if (strtolower($key) === 'phpunit/phpunit') {
                $value = '^' . implode(' || ^', $newPHPUnitVersions);
                break;
            }
        }

        file_put_contents(
            'composer.json',
            json_encode(
                $composer,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ) . PHP_EOL
        );

        exec('composer update phpunit/phpunit --with-dependencies');


        $output->writeln('PHP Version: ' . $php);
        $output->writeln('PHPUnit Version: ' . $phpunit . ' : ' . $from . '->' . $to);
        $output->writeln('Versions: ' . '^' . implode(' || ^', $newPHPUnitVersions));

        // replace getMock -> ... ?
        // replace $this->assert* to self::assert*
    }

    private function fileIterator() : Generator
    {
        $json = $this->getComposerJson();

        $autoload = $json['autoload-dev'] ?? [];

        foreach ($autoload as $type => $files) {
            if ($type === 'files') {
                $files = (array) $files;
                foreach ($files as $file) {
                    yield $file;
                }
            }

            if ($type === 'classmap') {
                $files = (array) $files;
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        yield from $this->files($file);
                    } elseif (is_file($file)) {
                        yield $file;
                    }
                }
            }

            if ($type === 'psr-0' || $type === 'psr-4') {
                foreach ($files as $namespace => $paths) {
                    $paths = (array) $paths;
                    foreach ($paths as $path) {
                        yield from $this->files($path);
                    }
                }
            }
        }
    }

    private function files(string $path) : Generator
    {
        $dir = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($dir);
        $regex = new RegexIterator($iterator, '/^.+\.php$/', RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $file) {
            yield $file[0];
        }
    }

    private function replaceTestCase(&$content)
    {
        $content = str_replace('PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase', $content);

        $content = preg_replace(
            '/extends\s+PHPUnit\\\\Framework\\\\TestCase/',
            'extends TestCase',
            $content
        );

        $content = preg_replace(
            '/((abstract\s+)?class\s+.*?extends\s+)\\\\PHPUnit\\\\Framework\\\\TestCase/',
            'use PHPUnit\Framework\TestCase;' . "\n\n" . '\\1' . 'TestCase',
            $content
        );

        $content = preg_replace(
            '/(use\s+[^;{]+?\\\\([^\s;]+?))\s+as\s+\2\s*;/i',
            '\\1;',
            $content
        );

        if (preg_match_all(
            '/->\s*setExpectedException\s*\(.+?\)\s*;/is',
            $content,
            $matches
        )) {
            foreach ($matches[0] as $ex) {
                $p = $this->getParams('setExpectedException', '$this' . $ex, ['name', 'message', 'code']);

                $replacement = sprintf('->expectException(%s);', $p['name']);
                if ($p['message']) {
                    $replacement .= "\n" . '        ' . sprintf('$this->expectExceptionMessage(%s);', $p['message']);
                }
                if ($p['code']) {
                    $replacement .= "\n" . '        ' . sprintf('$this->expectExceptionCode(%s);', $p['code']);
                }

                $content = str_replace($ex, $replacement, $content);
            }
        }

        if (preg_match_all(
            '/\$this\s*->\s*getMock\s*\(.+?\)\s*;/is',
            $content,
            $matches
        )) {
            foreach ($matches[0] as $m) {
                $p = $this->getParams(
                    'getMock',
                    $m,
                    [
                        'class',
                        'methods', // []
                        'args', // []
                        'name', // ''
                        'callOriginConstructor', // true
                        'callOriginalClone', // true
                        'callAutoload', // true
                        'cloneArguments', // false
                        'callOriginalMethods', // false
                        'proxyTarget', // null
                    ]
                );

                $replacement = sprintf('$this->getMock(%s)', $p['class']);

                // if ($p['methods'])
            }
        }

        // @expectedException
        // @expectedExceptionMessage
        // @expectedCode
    }

    private function getParams(string $name, string $ex, array $keys) : array
    {
        $ex = '<?php ' . $ex;

        $tokens = token_get_all($ex);

        $started = false;
        $open = null;
        $close = null;
        $stack = [];

        $map = [']' => '[', '}' => '{', ')' => '('];
        $params = [];
        $param = '';

        // todo: check long array notation
        foreach ($tokens as $token) {
            $content = is_array($token) ? $token[1] : $token;
            if (! $started) {
                if (strtolower($content) === strtolower($name)) {
                    $started = true;
                }
                continue;
            }

            if (! $open) {
                if ($content === '(') {
                    $open = true;
                }
                continue;
            }

            if (! $stack && is_array($token) && $token[0] === T_WHITESPACE) {
                continue;
            }

            if (in_array($content, ['[', ']', '{', '}', '(', ')'], true)) {
                if (in_array($content, ['[', '{', '('], true)) {
                    $stack[] = $content;
                } else {
                    if (! $stack && $content === ')') {
                        // $close = true;
                        $params[] = $param;
                        break;
                    }

                    $last = end($stack);

                    if ($map[$content] === $last) {
                        array_pop($stack);
                    }
                }
            }

            if (! $stack && $content === ',') {
                $params[] = $param;
                $param = '';
                continue;
            }

            $param .= $content;
        }

        $len = count($params);
        $keysLen = count($keys);
        if ($len < $keysLen) {
            $fill = array_fill($len, $keysLen - $len, null);
            $params += $fill;
        }

        return array_combine($keys, $params);
    }

    private function getPHP5Version(string $php) : ?string
    {
        if (Semver::satisfies('5.5', $php)) {
            throw new \Exception('Unsupported PHP Version');
        }

        if (Semver::satisfies('5.6', $php)) {
            return '5.6';
        }

        return null;
    }

    private function getPHP7Version(string $php) : ?string
    {
        if (Semver::satisfies('7.0', $php)) {
            return '7.0';
        }

        if (Semver::satisfies('7.1', $php)) {
            return '7.1';
        }

        if (Semver::satisfies('7.2', $php)) {
            return '7.2';
        }

        return null;
    }

    private function getPHPUnitVersions() : array
    {
        if ($this->versions) {
            return $this->versions;
        }

        $this->versionsJson = json_decode(file_get_contents('https://packagist.org/p/phpunit/phpunit.json'), true);

        foreach ($this->versionsJson['packages']['phpunit/phpunit'] as $version => $composer) {
            $v = explode('.', $version);
            if ($v[0] < 4 || VersionParser::parseStability($version) !== 'stable') {
                continue;
            }

            $this->versions[] = $version;
        }

        usort($this->versions, function($a, $b) {
            return $this->sortVersion($a, $b);
        });

        return $this->versions;
    }

    private function findPHPUnitVersion(string $php) : array
    {
        $versions = $this->getPHPUnitVersions();

        $php5 = $this->getPHP5Version($php);
        $php7 = $this->getPHP7Version($php);

        $result = [];
        foreach ($versions as $version) {
            $phpVer = $this->versionsJson['packages']['phpunit/phpunit'][$version]['require']['php'] ?? null;

            if ($php7 && Semver::satisfies($php7, $phpVer)) {
                $result[] = preg_replace('/\.0$/', '', $version);
                $php7 = null;
            }

            if ($php5 && Semver::satisfies($php5, $phpVer)) {
                $result[] = preg_replace('/\.0$/', '', $version);
                $php5 = null;
            }

            if (! $php5 && ! $php7) {
                break;
            }
        }

        return array_reverse($result);
    }

    private function findMinimumPHPUnitVersion(string $current) : ?string
    {
        $versions = array_reverse($this->getPHPUnitVersions());

        foreach ($versions as $version) {
            if (Semver::satisfies($version, $current)) {
                return $version;
            }
        }

        return null;
    }

    private function sortVersion($a, $b) : int
    {
        return Comparator::lessThan($a, $b) ? 1 : -1;
    }

    private function getPHPVersion() : ?string
    {
        $composer = $this->getComposerJson();

        return $composer['require']['php'] ?? null;
    }

    private function getPHPUnitVersion() : ?string
    {
        $composer = $this->getComposerJson();

        return $composer['require-dev']['phpunit/phpunit'] ?? null;
    }

    private function getComposerJson()
    {
        if (! $this->composer) {
            $this->composer = $this->lowercaseKeys(
                json_decode(file_get_contents('composer.json'), true)
            );
        }

        return $this->composer;
    }

    private function lowercaseKeys(array $input) : array
    {
        $return = [];
        foreach ($input as $key => $value) {
            $key = strtolower($key);

            if (is_array($value)) {
                $value = $this->lowercaseKeys($value);
            }

            $return[$key] = $value;
        }

        return $return;
    }
}
