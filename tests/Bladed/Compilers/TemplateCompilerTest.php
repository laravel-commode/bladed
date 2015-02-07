<?php
    namespace LaravelCommode\Bladed\Compilers;

    use Illuminate\Filesystem\Filesystem;

    class TemplateCompilerTest extends \PHPUnit_Framework_TestCase
    {
        protected function getViewMock()
        {
            return $this->getMock('\Illuminate\View\Factory', [], [], '', 0);
        }

        protected function getStringCompiler()
        {
            return new StringCompiler(new Filesystem(), '');
        }

        protected function getInstance($viewMock)
        {
            return new TemplateCompiler($this->getStringCompiler(), $viewMock);
        }

        public function testArguments()
        {
            $instance = $this->getInstance($view = $this->getViewMock());

            $args = ['a' => uniqid(), 'b' => uniqid()];

            $this->assertEmpty($instance->getArguments());
            $this->assertSame($args, $instance->setArguments($args)->getArguments());
            $instance->appendArguments(['c' => uniqid(), 'd' => uniqid()]);

            $args = $instance->getArguments();
            $this->assertTrue(array_key_exists('c', $args) && array_key_exists('d', $args));
        }

        public function testRender()
        {
            $instance = $this->getInstance($view = $this->getViewMock());

            $view->expects($this->exactly(3))->method('getShared')->will($this->returnValue([]));

            $args = ['var' => uniqid()];

            $template = "(:<ephp) (:var)var (:php>)";

            $instance->setTemplate($template);

            $result1 = $instance->render($args);

            $this->assertEquals($args['var'], $result1);

            $instance->setArguments($args = ['var' =>uniqid()]);

            $result2 = $instance->__toString();

            $this->assertEquals($args['var'], $result2);

            $this->assertNotEquals($result1, $result2);
            /*
            $template = "(:<ephp) [](:php>)";

            $this->setExpectedException('Exception');

            $instance->setTemplate($template)->render();*/
        }

        public function testTemplate()
        {
            $instance = $this->getInstance($view = $this->getViewMock());

            $this->assertSame('<?php echo $var; ?>', $instance->setTemplate("(:<php) echo (:var)var; (:php>)")->getTemplate());
            $this->assertSame('<?= $var ?>', $instance->setTemplate("(:<ephp) (:var)var (:php>)")->getTemplate());
        }
    }
