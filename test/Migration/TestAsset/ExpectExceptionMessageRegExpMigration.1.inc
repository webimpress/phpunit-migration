<?php

namespace WebimpressTest\PHPUnitMigration\Migration\TestAsset;

use PHPUnit\Framework\TestCase;

class ExpectExceptionMessageRegExpMigration extends TestCase
{
    public function testMultiline()
    {
        $this->expectExceptionMessageRegExp(
            'Exception Message 1'
        );
    }

    public function testSingleLine()
    {
        $this->expectExceptionMessageRegExp('Exception Message 2');
    }

    public function testPattern()
    {
        $pattern = '#[0-9]{2,}#i';

        $this->expectExceptionMessageRegExp($pattern);
    }
}
