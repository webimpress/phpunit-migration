<?php

namespace WebimpressTest\PHPUnitMigration\Migration\TestAsset;

use PHPUnit\Framework\TestCase;

class MoreReadableAssertionNameMigration extends TestCase
{
    public function testBetterAssertionNamesSelf()
    {
        self::assertNotIsReadable(__FILE__);
        self::assertNotIsWritable(__FILE__);
        self::assertDirectoryNotExists(__DIR__);
        self::assertDirectoryNotIsReadable(__DIR__);
        self::assertDirectoryNotIsWritable(__DIR__);
        self::assertFileNotExists(__FILE__);
        self::assertFileNotIsReadable(__FILE__);
        self::assertFileNotIsWritable(__FILE__);
        self::assertRegExp('/^xyz$/');
        self::assertNotRegExp('/^xyz$/');
    }

    public function testBetterAssertionNamesThis()
    {
        $this->assertNotIsReadable(__FILE__);
        $this->assertNotIsWritable(__FILE__);
        $this->assertDirectoryNotExists(__DIR__);
        $this->assertDirectoryNotIsReadable(__DIR__);
        $this->assertDirectoryNotIsWritable(__DIR__);
        $this->assertFileNotExists(__FILE__);
        $this->assertFileNotIsReadable(__FILE__);
        $this->assertFileNotIsWritable(__FILE__);
        $this->assertRegExp('/^xyz$/');
        $this->assertNotRegExp('/^xyz$/');
    }
}
