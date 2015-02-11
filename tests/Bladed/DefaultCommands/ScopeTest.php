<?php
    namespace LaravelCommode\Bladed\DefaultCommands\Scope;

    use LaravelCommode\Bladed\DefaultCommands\Scope;

    class ScopeTest extends \PHPUnit_Framework_TestCase
    {
        protected function mockApplication(array $methods = [])
        {
            return $this->getMock('Illuminate\Foundation\Application', $methods);
        }

        protected function mockView()
        {
            return $this->getMock('Illuminate\View\Factory', [], [], '', 0);
        }

        protected function getInstance($app, $view)
        {
            return (new Scope($app))->setEnvironment($view);
        }

        public function testSet()
        {
            $instance = $this->getInstance($app = $this->mockApplication(), $view = $this->mockView());

            $expectedValue = uniqid();

            $instance->set($fakeVar, $expectedValue);

            $this->assertEquals($expectedValue, $fakeVar);

            $instance->setIf($fakeVar, uniqid());

            $this->assertSame($expectedValue, $fakeVar);

            $instance->setIf($otherFakeVar, $otherExpectedValue = uniqid());

            $this->assertSame($otherExpectedValue, $otherFakeVar);
        }

        public function testShare()
        {
            $instance = $this->getInstance($app = $this->mockApplication(), $view = $this->mockView());

            $key = 'someKey';
            $value = uniqid();

            $view->expects($this->exactly(1))->method('share')->with($this->callback(function($gotKey) use ($key){
                $this->assertSame($key, $gotKey);
                return $key === $gotKey;
            }), $this->callback(function($gotValue) use ($value){
                $this->assertSame($value, $gotValue);
                return $value === $gotValue;
            }));

            $instance->share($key, $value);
        }

        public function testL()
        {
            $instance = $this->getInstance($app = $this->mockApplication(['make']), $view = $this->mockView());

            $translatorMock = $this->getMock('Illuminate\Translation\Translator', ['trans']);

            $id = 'validation.custom.someValue';
            $returnValue = uniqid();

            $translatorMock->expects($this->once())->method('trans')->with($id)->will($this->returnValue($returnValue));

            $app->expects($this->once())->method('make')->with('translator')->will($this->returnValue($translatorMock));

            \Illuminate\Support\Facades\Facade::setFacadeApplication($app);
            $this->assertSame($returnValue, $instance->l($id));
        }

        public function testDumps()
        {
            $instance = $this->getInstance($app = $this->mockApplication(), $view = $this->mockView());

            $value = uniqid();

            ob_clean();
            ob_start();
            var_dump($value);
            $expected = ob_get_clean();

            ob_clean();
            ob_start();
            $instance->var_dump($value);
            $real = ob_get_clean();

            $this->assertSame($expected, $real);
        }
    }
