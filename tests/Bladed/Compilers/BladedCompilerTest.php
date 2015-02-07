<?php
    namespace LaravelCommode\Bladed\Compilers;

    use Illuminate\Filesystem\Filesystem;
    use LaravelCommode\Bladed\Compilers\StringCompiler;
    use PHPUnit_Framework_MockObject_MockObject as MockObject;
    use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

    class TestClass {
        const TEST_VALUE = 'TEST_VALUE';

        public function testMethod()
        {
            return self::TEST_VALUE;
        }

        public static function callee()
        {
            return new static;
        }

    }

    class BladedCompilerTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @param callable $withMock
         * @return MockObject|\Illuminate\Foundation\Application
         */
        protected function getAppMock(\Closure $withMock = null)
        {
            $mock = $this->getMockBuilder('Illuminate\Foundation\Application');

            if ($withMock !== null) {
                $withMock($mock);
            }

            return $mock->getMock();
        }
        /**
         * @param callable $withMock
         * @return MockObject|\Illuminate\Filesystem\Filesystem
         */
        protected function getFilesMock(\Closure $withMock = null)
        {
            $mock = $this->getMockBuilder('Illuminate\Filesystem\Filesystem');

            if ($withMock !== null) {
                $withMock($mock);
            }

            return $mock->getMock();
        }

        /**
         * @param $fileMock
         * @param $path
         * @param callable $withMock
         * @return \LaravelCommode\Bladed\Compilers\StringCompiler|MockObject
         */
        protected function getStringCompilerMock($fileMock, $path, \Closure $withMock = null)
        {
            $mock = $this->getMockBuilder('LaravelCommode\Bladed\Compilers\StringCompiler');

            $mock->setConstructorArgs([$fileMock, $path]);

            if ($withMock !== null) {
                $withMock($mock);
            }

            return $mock->getMock();
        }

        /**
         * @param callable $withMock
         * @return MockObject|\Illuminate\View\Compilers\BladeCompiler
         */
        protected function getBladeMock(\Closure $withMock = null)
        {
            $mock = $this->getMockBuilder('Illuminate\View\Compilers\BladeCompiler');

            if ($withMock !== null) {
                $withMock($mock);
            }

            return $mock->getMock();
        }

        /**
         * @param callable $withMock
         * @return MockObject|\Illuminate\View\Factory
         */
        protected function getViewMock(\Closure $withMock = null)
        {
            $mock = $this->getMockBuilder('Illuminate\View\Factory');


            $mock->disableOriginalConstructor();
            if ($withMock !== null) {
                $withMock($mock);
            }


            return $mock->getMock();
        }

        /**
         * @param $app
         * @param $blade
         * @return BladedCompiler
         */
        protected function getInstance($app, $blade)
        {
            return new BladedCompiler($blade, $app);
        }

        public function testConstruct()
        {
            $app = $this->getAppMock();

            $blade = $this->getBladeMock(function (MockBuilder $builder) {
                $builder->disableOriginalConstructor();
            });

            $instance = $this->getInstance($app, $blade);

            $this->assertEquals('bladedCommand', $instance->getRegistryFunction());
            $this->assertEquals('bladed', $instance->getIoCRegistry());

            $expected = 'registry';
            $instance->setIoCRegistry($expected);
            $this->assertEquals($expected, $instance->getIoCRegistry());

            $expected = 'runThis';
            $instance->setRegistryFunction($expected);
            $this->assertEquals($expected, $instance->getRegistryFunction());
        }

        public function testCompiling()
        {
            $app = $this->getAppMock(function(MockBuilder $builder) {
                $builder->setMethods(['make']);
            });


            $blade = $this->getBladeMock(function (MockBuilder $builder) {
                $builder->disableOriginalConstructor();
            });


            $blade->expects($this->exactly(1))->method('extend')->will($this->returnCallback(function (\Closure $closure) use ($app) {
                $reflection = new \ReflectionFunction($closure);
                $rules = $reflection->getStaticVariables()['rules'];

                $scope = $reflection->getClosureThis();
                $reflectionScope = new \ReflectionClass($scope);

                $bladed = new \LaravelCommode\Bladed\Manager\BladedManager($scope, $app, new StringCompiler(new Filesystem(), ''));

                $app->expects($this->any())->method('make')->will($this->returnCallback(function ($make) use ($bladed) {

                    switch($make) {
                        case 'view':
                            return $this->getMock('\Illuminate\View\Factory', [], [], '', 0);
                            break;
                        case 'commode.bladed':
                            return $bladed;
                        default:

                    }
                }));

                $this->assertEquals(asort(array_values($reflectionScope->getConstants())), asort(array_keys($rules)));

                $this->assertEquals("<?php echo bladedCommand('ns', \$__env)->method() ?>", $closure("@ns.method() @>"));
                $this->assertEquals("<?php if(bladedCommand('ns', \$__env)->method()): ?>", $closure("@?ns.method() ?@>"));
                $this->assertEquals("<?php if(!bladedCommand('ns', \$__env)->method()): ?>", $closure("@!?ns.method() ?@>"));

                $this->assertEquals("<?php foreach(\$vars as \$var): ?>", $closure("@in(\$vars||\$var)"));
                $this->assertEquals("<?php foreach(\$vars as \$key => \$var): ?>", $closure("@in(\$vars||\$var||\$key)"));
                $this->assertEquals("<?php endforeach; ?>", $closure("@in>"));

                $this->assertEquals("<?php for(\$var=0;\$var<\$____length=count(\$vars);\$var++): ?>", $closure("@up(\$vars||\$var)"));
                $this->assertEquals("<?php for(\$var=0;\$var<\$____length=count(\$vars);\$var++): ?>\n<?php \$key = \$vars[\$var];?>", $closure("@up(\$vars||\$var||\$key)"));
                $this->assertEquals("<?php endfor; ?>", $closure("@up>"));

                $this->assertEquals("<?php for(\$var=count(\$vars)-1;\$var>0;\$var--): ?>", $closure("@down(\$vars||\$var)"));
                $this->assertEquals("<?php for(\$var=count(\$vars)-1;\$var>0;\$var--): ?>\n<?php \$key= \$vars[\$var];?>", $closure("@down(\$vars||\$var||\$key)"));
                $this->assertEquals("<?php endfor; ?>", $closure("@down>"));

                $template = "@|ns.method {text <?=\$var?> <?php echo \$var;?>}|([], \$var)@>";
                $expected = "<?php echo bladedCommand('ns', \$__env)".
                "->method((new \\LaravelCommode\\Bladed\\Compilers\\TemplateCompiler(\\Bladed::getStringCompiler()".
                ", \$__env))->setTemplate(\"text (:<ephp)(:var)var(:php>)".
                " (:<php) echo (:var)var;(:php>)\"), [], \$var) ?>";

                $this->assertSame($expected, $closure($template));

                $tmp = $scope->getRegistryFunction();
                $scope->setRegistryFunction("\\LaravelCommode\\Bladed\\Compilers\\TestClass::callee");

                $tpl = $closure($tpl = "@::someNS.testMethod() @>");
                $this->assertEquals(TestClass::TEST_VALUE, $tpl);
                $tpl = $closure($tpl = "@::|someNS.testMethod{ }|()@>");
                $this->assertEquals(TestClass::TEST_VALUE, $tpl);
            }));

            $this->getInstance($app, $blade);

        }

        public function testRegistries()
        {
            $app = $this->getAppMock(function (MockBuilder $builder) {
                $builder->setMethods(['singleton', 'make']);
            });


            $blade = $this->getBladeMock(function (MockBuilder $builder) {
                $builder->disableOriginalConstructor();
            });

            $app->expects($this->exactly(3))->method('singleton')->with($this->stringContains('.someNS', 0), $this->callback(function($closure){
                $this->assertTrue(($result = $closure()) instanceof TestClass);
                return ($result) instanceof TestClass;
            }));


            $app->expects($this->exactly(1))->method('make');

            $instance = $this->getInstance($app, $blade);

            $instance->registerNamespace('someNS', 'LaravelCommode\Bladed\Compilers\TestClass');
            $instance->registerNamespaces(
                ['someNS1' => 'LaravelCommode\Bladed\Compilers\TestClass',
                'someNS2' => 'LaravelCommode\Bladed\Compilers\TestClass']
            );

            $this->assertNull($instance->getNamespace('someNS'));

            $this->setExpectedException('Exception');

            $instance->getNamespace('FakeNS');
        }
    }
