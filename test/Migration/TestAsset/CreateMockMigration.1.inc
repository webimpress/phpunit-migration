<?php

namespace WebimpressTest\PHPUnitMigraton\Migration\TestAsset;

use PHPUnit\Framework\TestCase;

class CreateMockMigration extends TestCase
{
    public function testCreateMock1()
    {
        $mock = $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
    }

    public function testCreateMock2()
    {
        $mock = $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableArgumentCloning()
            ->disableOriginalClone()
            ->disallowMockingUnknownTypes()
            ->getMock();
    }

    public function testCreateMock3()
    {
        $mock = $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disallowMockingUnknownTypes()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();
    }

    public function testCreateMock4()
    {
        $mock = $this->getMockBuilder($originalClassName)
            ->disableArgumentCloning()
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disallowMockingUnknownTypes()
            ->getMock();
    }

    public function testCreateMock5()
    {
        $mock = $this->getMockBuilder($originalClassName)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCreateMock6()
    {
        $mock = $this
            ->getMockBuilder($originalClassName)->disableOriginalConstructor()->disableOriginalClone()
            ->disableArgumentCloning()->disallowMockingUnknownTypes()->getMock();
    }

    public function testCreateMock7()
    {
        $mock = $this->
            getMockBuilder(OriginalClass::class)->
            disableOriginalConstructor(

            )->
            disableOriginalClone (   )->
            disableArgumentCloning  ( )->
            disallowMockingUnknownTypes (  )->
            getMock ( )   ;
    }
}
