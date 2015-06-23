<?php
/**
 * Created by PhpStorm.
 * User: madman
 * Date: 21.06.15
 * Time: 3:00
 */

namespace LaravelCommode\Bladed\DefaultCommands;


use Illuminate\View\Factory;
use Illuminate\View\View;
use LaravelCommode\Bladed\Compilers\TemplateCompiler;
use LaravelCommode\Utils\Tests\PHPUnitContainer;

use PHPUnit_Framework_MockObject_MockObject as Mock;

class TemplateTest extends PHPUnitContainer
{
    /**
     * @var Factory|Mock
     */
    private $viewFactoryMock;

    /**
     * @var View|Mock
     */
    private $viewMock;

    /**
     * @var Template
     */
    private $testInstance;

    /**
     * @var TemplateCompiler|Mock
     */
    private $templateMock1;

    /**
     * @var TemplateCompiler|Mock
     */
    private $templateMock2;

    protected function setUp()
    {
        parent::setUp();

        $this->viewFactoryMock = $this->getMock(Factory::class, [], [], '', false);

        $this->testInstance = new Template($this->getApplicationMock());

        $this->testInstance->setEnvironment($this->viewFactoryMock);

        $this->viewMock = $this->getMock(View::class, [], [], '', false);

        $this->templateMock1 = $this->getMock(TemplateCompiler::class, [], [], '', false);
        $this->templateMock2 = $this->getMock(TemplateCompiler::class, [], [], '', false);
    }

    public function testAddRenderTemplate()
    {
        $this->templateMock2->expects($this->any())->method('setArguments')
            ->will($this->returnValue($this->templateMock2));

        $this->testInstance->add($this->templateMock1, 'first');
        $this->testInstance->add($this->templateMock2, 'second');

        $this->templateMock1->expects($this->once())->method('render')
            ->will($this->returnValue($mock1Val = uniqid()));

        $this->templateMock2->expects($this->any())->method('render')
            ->will($this->returnValue($mock2Val = uniqid()));

        $fakeArgs = ['varName' => uniqid('varName')];

        $this->assertSame($mock1Val, $this->testInstance->straightRender($this->templateMock1));
        $this->assertSame($mock2Val, $this->testInstance->render('second', $fakeArgs));

        $this->viewFactoryMock->expects($this->any())->method('make')
            ->with($viewName = uniqid('viewName'))
            ->will($this->returnValue($this->viewMock));

        $this->assertSame($mock2Val, $this->testInstance->renderOnce('second', $fakeArgs, $viewName));

    }

    protected function tearDown()
    {
        unset($this->testInstance, $this->viewFactoryMock);
        parent::tearDown();
    }
}
