<?php

namespace LaravelCommode\Bladed\Commands;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class ABladedCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Application|Mock
     */
    private $appMock;

    /**
     * @var ABladedCommand|Mock
     */
    private $testInstance;

    /**
     * @var Factory|Mock
     */
    private $viewMock;

    protected function setUp()
    {
        $this->appMock = $this->getMock(Application::class, []);
        $this->viewMock = $this->getMock(Factory::class, []);
        $this->testInstance = $this->getMockForAbstractClass(ABladedCommand::class, [$this->appMock]);

        parent::setUp();
    }

    public function testGetSetApplicationAndEnv()
    {
        $this->assertSame($this->appMock, $this->testInstance->getApplication());

        $this->appMock->expects($this->once())->method('make')
            ->with('view')
            ->will($this->returnValue($this->viewMock));

        $this->assertSame($this->viewMock, $this->testInstance->getEnvironment());
        $this->testInstance->setEnvironment($this->viewMock);
        $this->assertSame($this->viewMock, $this->testInstance->getEnvironment());
    }

    public function testExtend()
    {
        $returnValue = uniqid('return');

        $this->testInstance->extend('method', function () use ($returnValue) {
            return $returnValue;
        }, true);

        $this->assertSame($returnValue, $this->testInstance->method());

        try {
            $this->testInstance->nonExistantMethod();
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \BadMethodCallException);
        }
    }

    protected function tearDown()
    {
        unset($this->appMock, $this->testInstance);
        parent::tearDown();
    }
}
