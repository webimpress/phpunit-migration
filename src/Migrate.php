<?php

namespace Webimpress\PHPUnitMigration;

use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use InvalidArgumentException;
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
            $output->writeln('<error>Cannot detec PHP version in your composer.json</error>');
            return 1;
        }

        $minPHPUnitVersion = $this->findMinimumPHPUnitVersion($phpunit);
        $newPHPUnitVersions = $this->findPHPUnitVersion($php);

        $from = explode('.', $minPHPUnitVersion)[0];
        $to = explode('.', $newPHPUnitVersions[0])[0];

        $output->writeln('PHP Version: ' . $php);
        $output->writeln('PHPUnit Version: ' . $phpunit . ' : ' . $from . '->' . $to);
        $output->writeln('Versions: ' . '^' . implode(' || ^', $newPHPUnitVersions));


        // check current PHPUnit version in composer.json
        // check current PHP version supported in composer.json

        // decide what PHPUnit version to use
        // check if we can get the latest version from packagist?
        // https://packagist.org/p/phpunit/phpunit.json

        // loop through all files (autoload-dev section from composer?)
        // - replace PHPUnit_Framework_TestCase
        // - import PHPUnit\Framework\TestCase
        // - remove alias if there is TestCase
        //

    }

    private function getPHP5Version(?string $php) : ?string
    {
        if (Semver::satisfies('5.6', $php)) {
            return '5.6';
        }

        return null;
    }

    private function getPHP7Version(?string $php) : ?string
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
