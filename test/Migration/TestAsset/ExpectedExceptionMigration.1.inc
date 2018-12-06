<?php

namespace WebimpressTest\PHPUnitMigration\Migration\TestAsset;

use PHPUnit\Framework\TestCase;

class ExpectedExceptionMigration extends TestCase
{
    public function testWithThreeParams()
    {
        $this->setExpectedException(
            \Exception::class,
            'Message 3',
            123
        );
    }

    public function testWithTwoParams()
    {
        $this->setExpectedException(\DomainException::class, 'Message 2');
    }

    public function testWithOneParam()
    {
        $this->setExpectedException(\ErrorException::class);
    }

    public function testRegexpWithThreeParams()
    {
        $this->setExpectedExceptionRegExp(
            \InvalidArgumentException::class,
            '/[a-z]+ $/',
            456
        );
    }

    public function testRegexpWithTwoParams()
    {
        $this->setExpectedExceptionRegExp(\OutOfRangeException::class, '#[0-9]{2,}#i');
    }

    public function testRegexpWithOneParam()
    {
        $this->setExpectedExceptionRegExp(\RuntimeException::class);
    }
}
