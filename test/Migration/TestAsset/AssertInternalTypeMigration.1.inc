<?php

namespace WebimpressTest\PHPUnitMigraton\Migration\TestAsset;

use PHPUnit\Framework\TestCase;

class AssertInternalTypeMigration extends TestCase
{
    public function testArray()
    {
        $this->assertInternalType('array', $a, 'Message array');
        $this->assertInternalType('array', $b);

        $this->assertNotInternalType('array', $c, 'Message not array');
        $this->assertNotInternalType('array', $d);
    }

    public function testBool()
    {
        $this->assertInternalType('bool', $a, 'Message bool');
        $this->assertInternalType('boolean', $b, 'Message boolean');
        $this->assertInternalType('bool', $c);
        $this->assertInternalType('boolean', $d);

        $this->assertNotInternalType('bool', $e, 'Message not bool');
        $this->assertNotInternalType('boolean', $f, 'Message not boolean');
        $this->assertNotInternalType('bool', $g);
        $this->assertNotInternalType('boolean', $h);
    }

    public function testFloat()
    {
        $this->assertInternalType('double', $a, 'Message double');
        $this->assertInternalType('float', $b, 'Message float');
        $this->assertInternalType('real', $c, 'Message real');
        $this->assertInternalType('double', $d);
        $this->assertInternalType('float', $e);
        $this->assertInternalType('real', $f);

        $this->assertNotInternalType('double', $g, 'Message not double');
        $this->assertNotInternalType('float', $h, 'Message not float');
        $this->assertNotInternalType('real', $i, 'Message not real');
        $this->assertNotInternalType('double', $j);
        $this->assertNotInternalType('float', $k);
        $this->assertNotInternalType('real', $l);
    }

    public function testInt()
    {
        $this->assertInternalType('int', $a, 'Message int');
        $this->assertInternalType('integer', $b, 'Message integer');
        $this->assertInternalType('int', $c);
        $this->assertInternalType('integer', $d);

        $this->assertNotInternalType('int', $e, 'Message not int');
        $this->assertNotInternalType('integer', $f, 'Message not integer');
        $this->assertNotInternalType('int', $g);
        $this->assertNotInternalType('integer', $h);
    }

    public function testNull()
    {
        $this->assertInternalType('null', $a, 'Message null');
        $this->assertInternalType('null', $b);

        $this->assertNotInternalType('null', $c, 'Message not null');
        $this->assertNotInternalType('null', $d);
    }

    public function testNumeric()
    {
        $this->assertInternalType('numeric', $a, 'Message numeric');
        $this->assertInternalType('numeric', $b);

        $this->assertNotInternalType('numeric', $c, 'Message not numeric');
        $this->assertNotInternalType('numeric', $d);
    }

    public function testObject()
    {
        $this->assertInternalType('object', $a, 'Message object');
        $this->assertInternalType('object', $b);

        $this->assertNotInternalType('object', $c, 'Message not object');
        $this->assertNotInternalType('object', $d);
    }

    public function testResource()
    {
        $this->assertInternalType('resource', $a, 'Message resource');
        $this->assertInternalType('resource', $b);

        $this->assertNotInternalType('resource', $c, 'Message not resource');
        $this->assertNotInternalType('resource', $d);
    }

    public function testString()
    {
        $this->assertInternalType('string', $a, 'Message string');
        $this->assertInternalType('string', $b);

        $this->assertNotInternalType('string', $c, 'Message not string');
        $this->assertNotInternalType('string', $d);
    }

    public function testScalar()
    {
        $this->assertInternalType('scalar', $a, 'Message scalar');
        $this->assertInternalType('scalar', $b);

        $this->assertNotInternalType('scalar', $c, 'Message not scalar');
        $this->assertNotInternalType('scalar', $d);
    }

    public function testCallable()
    {
        $this->assertInternalType('callable', $a, 'Message callable');
        $this->assertInternalType('callable', $b);

        $this->assertNotInternalType('callable', $c, 'Message not callable');
        $this->assertNotInternalType('callable', $d);
    }

    public function testIterable()
    {
        $this->assertInternalType('iterable', $a, 'Message iterable');
        $this->assertInternalType('iterable', $b);

        $this->assertNotInternalType('iterable', $c, 'Message not iterable');
        $this->assertNotInternalType('iterable', $d);

        $this->assertNotInternalType(
            'iterable',
            $variableName,
            sprintf('Message something %s ... %d', $a, $b)
        );
    }
}
