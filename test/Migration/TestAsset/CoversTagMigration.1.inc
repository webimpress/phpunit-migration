<?php

namespace WebimpressTest\PHPUnitMigraton\Migration\TestAsset;

use PHPUnit\Framework\TestCase;

class CoversTagMigration extends TestCase
{
    /**
     * @covers MyClass
     */
    public function testMissingLeadingSlash()
    {
    }

    /**
     * @covers   OtherClass
     */
    public function testMoreThanOneSpaceAfterTag()
    {
    }

    /**
     * @covers \LikeThatItIsFine
     */
    public function testWithLeadingSlash()
    {
    }
}
