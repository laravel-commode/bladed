<?php
    namespace LaravelCommode\Bladed\DefaultCommands;


    use PHPUnit_Framework_MockObject_MockObject;

    class TemplateTest extends \PHPUnit_Framework_TestCase
    {
        protected function getInstance($appMock)
        {
            return new Template($appMock);
        }

        protected function getAppMock(array $methods = [])
        {
            return $this->getMock('Illuminate\Foundation\Application', $methods, [], '', false);
        }

        protected function getTemplateCompilerMock(array $methods = [])
        {
            return $this->getMock('LaravelCommode\Bladed\Compilers\TemplateCompiler', $methods, [], '', false);
        }

        protected function getViewMock(array $methods = [])
        {
            return $this->getMock('Illuminate\View\View', $methods, [], '', false);
        }

        protected function getViewFactoryMock(array $methods = [])
        {
            return $this->getMock('Illuminate\View\Factory', [], [], '', false);
        }

        protected function composeRender($viewName, PHPUnit_Framework_MockObject_MockObject $viewFactory, PHPUnit_Framework_MockObject_MockObject $view)
        {
            $view->expects($this->any())->method('render');

            $viewFactory->expects($this->once())->method('make')->with($this->callback(function ($name) use ($viewName){
                $this->assertSame($viewName, $name);
                return $name === $viewName;
            }))->will($this->returnValue($view));
        }

        public function testLoad()
        {
            $instance = $this->getInstance($app = $this->getAppMock());

            $viewName = uniqid();

            $this->composeRender(
                $viewName,
                $viewFactory = $this->getViewFactoryMock(['make']),
                $view = $this->getViewMock(['render'])
            );

            $instance->setEnvironment($viewFactory);

            $instance->load($viewName);
        }

        public function testRenders()
        {
            $viewName = uniqid();
            $viewFactory = $this->getViewFactoryMock(['make']);
            $view = $this->getViewMock(['render', '__toString']);
            $this->composeRender($viewName, $viewFactory, $view);

            $instance = $this->getInstance($app = $this->getAppMock());
            $instance->setEnvironment($viewFactory);

            $templateCompiler = $this->getTemplateCompilerMock();

            $templateValue = uniqid();

            $templateCompiler->expects($this->atLeastOnce())->method('setArguments')->will($this->returnValue($templateCompiler));
            $templateCompiler->expects($this->atLeastOnce())->method('render')->will($this->returnValue($templateValue));
            $instance->add($templateCompiler, $templateName = uniqid(), ['a' => $templateParam1 = uniqid()]);



            $this->assertSame($templateValue, $instance->render($templateName));
            $this->assertSame($templateValue, $instance->renderOnce($templateName));
            $this->assertSame($templateValue, $instance->renderOnce($templateName, [], $viewName));
            $this->assertSame($templateValue, $instance->straightRender($templateCompiler));
        }
    }
