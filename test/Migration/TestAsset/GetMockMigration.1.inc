<?php

namespace Webimpress\PHPUnitMigration\Migration\TestAsset;

use PHPUnit\Framework\TestCase;

class GetMockMigration extends TestCase
{
    public function testWithAllParametersDefaultValues()
    {
        $mock = $this->getMock(
            self::class,
            [],
            [],
            '',
            true,
            true,
            true,
            false,
            false,
            null
        );
    }

    public function testWithNonDefaultValues()
    {
        $mock = $this->getMock(
            self::class,
            null,
            ['arg1', 'arg2'],
            'MockClassName',
            false,
            false,
            false,
            true,
            true,
            new \DateTime()
        );
    }

    public function testWithRequiredParam()
    {
        $mock = $this->getMock(self::class);
    }

    public function testWithDisabledConstructor()
    {
        $mock = $this->getMock(self::class, [], [], '', false);
    }

    public function testWithAllMethods()
    {
        $mock = $this->getMock(self::class, null);
    }

    public function testWithSomeMethods()
    {
        $this->call(
            $this->getMock(
                self::class,
                ['method1', 'method2'],
                [],
                '',
                false
            )
        );
    }
}
