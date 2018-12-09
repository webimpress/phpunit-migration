<?php

declare(strict_types=1);

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
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webimpress\PHPUnitMigration\Migration\AssertInternalTypeMigration;
use Webimpress\PHPUnitMigration\Migration\CoversTagMigration;
use Webimpress\PHPUnitMigration\Migration\ExpectedExceptionMigration;
use Webimpress\PHPUnitMigration\Migration\GetMockMigration;
use Webimpress\PHPUnitMigration\Migration\SetUpMigration;
use Webimpress\PHPUnitMigration\Migration\TearDownMigration;
use Webimpress\PHPUnitMigration\Migration\TestCaseMigration;

use function array_reverse;
use function exec;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function get_class;
use function getcwd;
use function implode;
use function is_array;
use function is_dir;
use function is_file;
use function json_decode;
use function json_encode;
use function preg_replace;
use function realpath;
use function round;
use function sprintf;
use function strpos;
use function strstr;
use function strtolower;
use function usort;
use function version_compare;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;

class Migrate extends Command
{
    /** @var array */
    private $composer;

    /** @var array array */
    private $versions = [];

    /** @var array */
    private $versionsJson;

    /** @var float */
    private $php7max = 7.3;

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
            )
            ->addArgument(
                'iterations',
                InputArgument::OPTIONAL,
                'Number of iterations each migration will be executed',
                1
            );
    }

    /**
     * @throws InvalidArgumentException
     */
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

        $iterations = (int) $input->getArgument('iterations');
        if ($iterations < 1) {
            throw new InvalidArgumentException('Number of iterations cannot be lower than 1');
        }
    }

    /**
     * @return int
     */
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

        $iterations = (int) $input->getArgument('iterations');

        $minPHPUnitVersion = $this->findMinimumPHPUnitVersion($phpunit);
        $newPHPUnitVersions = $this->findPHPUnitVersion($php);

        $from = explode('.', $minPHPUnitVersion)[0];
        $to = explode('.', $newPHPUnitVersions[0])[0];

        foreach ($this->fileIterator() as $file) {
            if ($to >= 5) {
                $content = $this->replaceTestCase($file, $newPHPUnitVersions[0], $iterations);
                file_put_contents($file, $content);
            }
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
        $output->writeln('Versions: ^' . implode(' || ^', $newPHPUnitVersions));

        // replace getMock -> ... ?
        // replace $this->assert* to self::assert*

        return 0;
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

    private function replaceTestCase(string $fileName, string $phpUnitVersion, int $iterations) : string
    {
        $content = file_get_contents($fileName);

        $migrations = [
            new AssertInternalTypeMigration(),
            new CoversTagMigration(),
            new ExpectedExceptionMigration(),
            new GetMockMigration(),
            new SetUpMigration(),
            new TearDownMigration(),
            new TestCaseMigration(),
        ];

        for ($i = 1; $i <= $iterations; ++$i) {
            foreach ($migrations as $migration) {
                if (version_compare($phpUnitVersion, $migration::PHPUNIT_VERSION_REQUIRED) >= 0) {
                    echo sprintf('[%d] Run migration %s on file %s', $i, get_class($migration), $fileName), PHP_EOL;
                    $content = $migration->migrate($content);
                }
            }
        }

        // $content = preg_replace('/\$this\s*->\s*assert/', 'self::assert', $content);

        // @expectedException
        // @expectedExceptionMessage
        // @expectedCode

        return $content;
    }

    /**
     * @throws RuntimeException
     */
    private function getPHP5Version(string $php) : ?string
    {
        if (Semver::satisfies('5.5', $php)) {
            throw new RuntimeException('Unsupported PHP Version');
        }

        if (Semver::satisfies('5.6', $php)) {
            return '5.6';
        }

        return null;
    }

    private function getPHP7Version(string $php) : ?string
    {
        $max = round($this->php7max * 10);
        for ($i = 70; $i <= $max; ++$i) {
            $v = sprintf('%.1f', 0.1 * $i);
            if (Semver::satisfies($v, $php)) {
                return $v;
            }
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

        usort($this->versions, function ($a, $b) {
            return $this->sortVersion($a, $b);
        });

        return $this->versions;
    }

    private function findPHPUnitVersion(string $php) : array
    {
        $versions = $this->getPHPUnitVersions();

        $php5 = $this->getPHP5Version($php);
        $php7min = $this->getPHP7Version($php);
        $php7 = $php7min ? $this->php7max : null;

        $result = [];
        foreach ($versions as $version) {
            $phpVer = $this->versionsJson['packages']['phpunit/phpunit'][$version]['require']['php'] ?? null;

            if ($php7 && Semver::satisfies($php7, $phpVer)) {
                $result[] = preg_replace('/\.0$/', '', $version);
                $php7 = sprintf('%.1f', $php7 - 0.1);
                if ($php7 < $php7min) {
                    $php7 = null;
                }
            }

            if ($php5 && Semver::satisfies($php5, $phpVer)) {
                $result[] = preg_replace('/\.0$/', '', $version);
                $php5 = null;
            }

            if (! $php5 && ! $php7) {
                break;
            }
        }

        $result = array_reverse($result);
        foreach ($result as $k => $ver) {
            if (isset($result[$k + 1]) && strpos($result[$k + 1], strstr($ver, '.', true)) === 0) {
                unset($result[$k]);
            }
        }

        sort($result);

        return $result;
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

    private function sortVersion(string $a, string $b) : int
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

    private function getComposerJson() : array
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
            $key = strtolower((string) $key);

            if (is_array($value)) {
                $value = $this->lowercaseKeys($value);
            }

            $return[$key] = $value;
        }

        return $return;
    }
}
