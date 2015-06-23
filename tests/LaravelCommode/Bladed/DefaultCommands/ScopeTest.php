<?php

namespace LaravelCommode\Bladed\DefaultCommands;

use Illuminate\View\Factory;

use LaravelCommode\Utils\Tests\PHPUnitContainer;

use PHPUnit_Framework_MockObject_MockObject as Mock;

class ScopeTest extends PHPUnitContainer
{
    /**
     * @var Scope
     */
    private $testInstance;

    /**
     * @var Factory|Mock
     */
    private $viewMock;

    /**
     * @var \Illuminate\Translation\Translator|Mock
     */
    private $translatorMock;

    protected function applicationMocksMethods()
    {
        return ['getLocale'];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->viewMock = $this->getMock(Factory::class, [], [], '', false);
        $this->testInstance = new Scope($this->getApplicationMock());
        $this->translatorMock = $this->getMock('Illuminate\Translation\Translator', ['trans'], [], '', false);
        $this->testInstance->setEnvironment($this->viewMock);
    }

    public function testSets()
    {
        $this->testInstance->set($var, $value = uniqid());
        $this->assertSame($value, $var);

        $varExists = 5;
        $this->testInstance->setIf($varExists, $value = uniqid());

        $this->testInstance->setIf($newVar, $value = uniqid());
        $this->assertSame($value, $newVar);
    }

    public function testShare()
    {
        $value = uniqid('value');

        $this->viewMock->expects($this->once())->method('share')
            ->with('var', $value);

        $this->testInstance->share('var', $value);
    }

    public function testLang()
    {
        $this->translatorMock->expects($this->once())->method('trans')
            ->with('someValue');

        $this->getApplicationMock()->expects($this->once())->method('make')
            ->will($this->returnValue($this->translatorMock));

        $this->testInstance->l('someValue');
    }

    public function testDump()
    {
        $var = ['a' => uniqid()];
        ob_start();
        var_dump($var);
        $output1 = ob_get_clean();

        ob_start();
        $this->testInstance->dump($var);
        $output2 = ob_get_clean();

        $this->assertSame($output1, $output2);
    }

    protected function tearDown()
    {
        unset($this->testInstance);
        parent::tearDown();
    }
}
