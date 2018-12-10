<?php

namespace WebimpressTest\PHPUnitMigraton\Migration\TestAsset;

use PHPUnit\Framework\TestCase;

class AssertEqualsMigration extends TestCase
{
    public function testNotChanged()
    {
        $this->assertEquals($a, $b);
        $this->assertEquals($a, $b, '');
        $this->assertEquals($a, $b, 'Message equals');
        $this->assertEquals($a, $b, 'Message equals default', 0.0, 10, false, false);
        $this->assertEquals($a, $b, 'Message equals maxDepth', 0.0, 13, false, false);

        $this->assertNotEquals($a, $b);
        $this->assertNotEquals($a, $b, '');
        $this->assertNotEquals($a, $b, 'Message not equals');
        $this->assertNotEquals($a, $b, 'Message not equals default', 0.0, 10, false, false);
        $this->assertNotEquals($a, $b, 'Message not equals maxDepth', 0.0, 13, false, false);
    }

    public function testAssertEqualsWithDelta()
    {
        $this->assertEquals($a, $b, 'Message equals with delta', 0.1);
        $this->assertEquals(
            $a,
            $b,
            '',
            0.2
        );
        $this->assertEquals($a, $b, 'Message equals with delta', 0.3, 10, true, true);

        $this->assertNotEquals($a, $b, 'Message not equals with delta', 0.1);
        $this->assertNotEquals(
            $a,
            $b,
            '',
            0.2
        );
        $this->assertNotEquals($a, $b, 'Message not equals with delta', 0.3, 10, true, true);
    }

    public function testAssertEqualsCanonicalizing()
    {
        $this->assertEquals($a, $b, 'Message equals canonicalizing', 0.0, 10, true);
        $this->assertEquals(
            $a,
            $b,
            '',
            0.0,
            13,
            true
        );
        $this->assertEquals($a, $b, 'Message equals canonicalizing', 0.0, 10, true, true);

        $this->assertNotEquals($a, $b, 'Message not equals canonicalizing', 0.0, 10, true);
        $this->assertNotEquals(
            $a,
            $b,
            '',
            0.0,
            13,
            true
        );
        $this->assertNotEquals($a, $b, 'Message not equals canonicalizing', 0.0, 10, true, true);
    }

    public function testAssertEqualsIgnoringCase()
    {
        $this->assertEquals($a, $b, 'Message equals ignoring case', 0.0, 10, false, true);
        $this->assertEquals(
            $a,
            $b,
            '',
            0.0,
            13,
            false,
            true
        );
        $this->assertEquals($a, $b, 'Message equals ignoring case', 0.0, 10, false, true);

        $this->assertNotEquals($a, $b, 'Message not equals ignoring case', 0.0, 10, false, true);
        $this->assertNotEquals(
            $a,
            $b,
            '',
            0.0,
            13,
            false,
            true
        );
        $this->assertNotEquals($a, $b, 'Message not equals ignoring case', 0.0, 10, false, true);
    }
}
