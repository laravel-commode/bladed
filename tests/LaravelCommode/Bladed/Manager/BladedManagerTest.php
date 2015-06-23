<?php

namespace LaravelCommode\Bladed\Manager;

use Illuminate\Contracts\Foundation\Application;

use Illuminate\View\Factory;
use LaravelCommode\Bladed\BladedServiceProvider;
use LaravelCommode\Bladed\Commands\ABladedCommand;
use LaravelCommode\Bladed\Commands\ABladedCommandTest;
use LaravelCommode\Bladed\Compilers\BladedCompiler;
use LaravelCommode\Bladed\Compilers\StringCompiler;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class BladedManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BladedManager
     */
    private $testInstance;

    /**
     * @var Application|Mock
     */
    private $appMock;

    /**
     * @var BladedCompiler|Mock
     */
    private $bladedCompilerMock;

    /**
     * @var StringCompiler|Mock
     */
    private $stringCompilerMock;

    /**
     * @var Factory|Mock
     */
    private $viewMock;

    protected function setUp()
    {
        $this->appMock = $this->getMock(Application::class, []);
        $this->bladedCompilerMock = $this->getMock(BladedCompiler::class, [], [], '', false);
        $this->stringCompilerMock = $this->getMock(StringCompiler::class, [], [], '', false);
        $this->viewMock = $this->getMock(Factory::class, [], [], '', false);
        $this->testInstance = new BladedManager($this->bladedCompilerMock, $this->appMock, $this->stringCompilerMock);
        parent::setUp();
    }

    public function testGetCommand()
    {
        $makeMock = $this->getMockForAbstractClass(ABladedCommand::class, [$this->appMock]);

        $testName = uniqid();

        $this->appMock->expects($this->any())->method('bound')
            ->with()
            ->will($this->returnCallback(function ($boundCheck) use ($testName) {
                return $boundCheck === BladedServiceProvider::PROVIDES_SERVICE.'.'.$testName;
            }));

        $this->bladedCompilerMock->expects($this->exactly(2))->method('getIocRegistry')
            ->will($this->returnValue(BladedServiceProvider::PROVIDES_SERVICE));

        $this->appMock->expects($this->any())->method('make')
            ->will($this->returnCallback(function () use ($makeMock) {
                return $makeMock;
            }));

        $this->testInstance->getCommand($testName, $this->viewMock);

        try {
            $this->testInstance->getCommand($fake = uniqid('fake'), $this->viewMock);
        } catch (\Exception $e) {
            $this->assertSame("Unable to resolve \"{$fake}\" command stack.", $e->getMessage());
        }
    }

    public function testExtend()
    {
        $commandMock = $this->getMockForAbstractClass(ABladedCommand::class, [$this->appMock]);
        $testName = uniqid();
        $methodName = uniqid('method');

        $extendCallback = function () {

        };

        $this->appMock->expects($this->any())->method('bound')
            ->will($this->returnValue(true));

        $this->appMock->expects($this->any())->method('make')
            ->will($this->returnValue($commandMock));


        $this->testInstance->extendCommand($testName, $methodName, $extendCallback, false);
    }

    public function testRegistrations()
    {
        $fakeCommandPairs = [
            'fake1' => 'FakeClass1',
            'fake2' => 'FakeClass2'
        ];

        $this->testInstance->registerCommandNamespaces($fakeCommandPairs);
    }

    protected function tearDown()
    {
        unset(
            $this->testInstance,
            $this->stringCompilerMock,
            $this->viewMock,
            $this->bladedCompilerMock,
            $this->appMock
        );

        parent::tearDown();
    }
}
