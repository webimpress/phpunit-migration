<?php

declare(strict_types=1);

namespace WebimpressTest\PHPUnitMigration\Migration;

use Generator;
use PHPUnit\Framework\TestCase;
use Webimpress\PHPUnitMigration\Migration\AbstractMigration;

use function basename;
use function file_exists;
use function file_get_contents;
use function glob;
use function sprintf;
use function strpos;
use function substr;

class MigrationTest extends TestCase
{
    public function migrations() : Generator
    {
        $migrations = glob(__DIR__ . '/../../src/Migration/*Migration.php');

        foreach ($migrations as $migration) {
            $migration = substr(basename($migration), 0, -4);
            if (strpos($migration, 'Abstract') === 0) {
                continue;
            }

            $files = glob(__DIR__ . '/TestAsset/' . $migration . '.*.inc');
            foreach ($files as $file) {
                yield $migration . '::' . $file => [$migration, $file];
            }

            if (! $files) {
                $this->markTestIncomplete(sprintf(
                    'Missing TestAsset for migration %s',
                    $migration
                ));
            }
        }
    }

    /**
     * @dataProvider migrations
     */
    public function testMigration(string $migrationName, string $fileName) : void
    {
        $className = '\\Webimpress\\PHPUnitMigration\\Migration\\' . $migrationName;

        /** @var AbstractMigration $migration */
        $migration = new $className();

        $input = file_get_contents($fileName);

        if (! file_exists($fileName . '.fixed')) {
            $this->markTestSkipped('Missing fixed file.');
        }

        $output = file_get_contents($fileName . '.fixed');

        $this->assertSame($output, $migration->migrate($input));
    }
}
