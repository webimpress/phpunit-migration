<?php

namespace WebimpressTest\PHPUnitMigration\Migration\TestAsset;

use PHPUnit\Framework\TestCase;

class VoidReturnTypeMigration extends TestCase
{
    public function setUp(): void
    {
    }

    protected function tearDown()  // comment
    {
    }

    public static function setUpBeforeClass()
        : void
    {
    }

    public static function tearDownAfterClass()
        // comment
    {
    }
}
