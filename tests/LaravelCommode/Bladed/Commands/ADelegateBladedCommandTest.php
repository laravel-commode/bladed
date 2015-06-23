<?php

namespace LaravelCommode\Bladed\Commands;

use Illuminate\Contracts\Foundation\Application;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class ADelegateBladedCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $testValue;

    /**
     * @var Application|Mock
     */
    private $appMock;

    /**
     * @var ADelegateBladedCommand|Mock
     */
    private $testInstance;

    protected function setUp()
    {
        $this->appMock = $this->getMock(Application::class, []);
        $this->testInstance = $this->getMockForAbstractClass(ADelegateBladedCommand::class, [$this->appMock]);
        $this->testValue = uniqid('TestValue');

        parent::setUp();
    }

    public function methodToDelegate()
    {
        return $this->testValue;
    }

    public function testDelegateCall()
    {
        $this->testInstance->expects($this->exactly(2))->method('getDelegate')
            ->will($this->returnValue($this));

        $this->testInstance->extend('extension', function () {
            return $this->testValue;
        });

        $this->assertSame($this->testValue, $this->testInstance->methodToDelegate());
        $this->assertSame($this->testValue, $this->testInstance->extension());

    }
}
