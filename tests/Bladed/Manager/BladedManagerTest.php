<?php
    namespace LaravelCommode\Bladed\Manager;

    use LaravelCommode\Bladed\Commands\ABladedCommand;
    use PHPUnit_Framework_MockObject_MockObject as MockObject;
    use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;


    class BladedManagerTest extends \PHPUnit_Framework_TestCase
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
         * @param $appMock
         * @param $bladeMock
         * @param callable $withMock
         * @return \LaravelCommode\Bladed\Compilers\BladedCompiler|MockObject
         */
        protected function getBladedMock($appMock, $bladeMock, \Closure $withMock = null)
        {
            $mock = $this->getMockBuilder('LaravelCommode\Bladed\Compilers\BladedCompiler');

            $mock->setConstructorArgs([$bladeMock, $appMock]);

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
         * @param callable $withMock
         * @return MockObject|ABladedCommand
         */
        protected function getCommandMock($app, $construct = true, \Closure $withMock = null)
        {
            $mock = $this->getMockForAbstractClass(ABladedCommand::class, [$app], '', $construct);


            if ($withMock !== null) {
                $withMock($mock);
            }


            return $mock;
        }

        public function getInstance($bladeMock, $appMock, $stringManagerMock)
        {
            return new BladedManager($bladeMock, $appMock, $stringManagerMock);
        }

        public function testConstruct()
        {
            $app = $this->getAppMock();

            $fileMock = $this->getFilesMock();
            $stringCompiler = $this->getStringCompilerMock($fileMock, 'views');

            $blade = $this->getBladeMock(function(MockBuilder $builder){
                $builder->disableOriginalConstructor();
            });

            $blade->expects($this->any())->method('extend');

            $bladed = $this->getBladedMock($app, $blade);

            $instance = $this->getInstance($bladed, $app, $stringCompiler);

            $instanceReflection = new \ReflectionClass($instance);
            $propertyBladed = $instanceReflection->getProperty('bladed');
            $propertyBladed->setAccessible(true);

            $propertyApplication = $instanceReflection->getProperty('application');
            $propertyApplication->setAccessible(true);

            $this->assertEquals($bladed, $propertyBladed->getValue($instance));
            $this->assertEquals($app, $propertyApplication->getValue($instance));

            $this->assertEquals($stringCompiler, $instance->getStringCompiler());
        }

        public function testRegistry()
        {
            $app = $this->getAppMock();

            $fileMock = $this->getFilesMock();
            $stringCompiler = $this->getStringCompilerMock($fileMock, 'views');

            $blade = $this->getBladeMock(function(MockBuilder $builder){
                $builder->disableOriginalConstructor();
            });

            $blade->expects($this->any())->method('extend');

            $bladed = $this->getBladedMock($app, $blade);

            $bladed->expects($this->exactly(3))->method('registerNamespace');

            $instance = $this->getInstance($bladed, $app, $stringCompiler);

            $instance->registerCommandNamespace('JS', 'SomeJSClass');

            $instance->registerCommandNamespaces([
                'Scope' => 'ScopeClass',
                'Form' => 'FormClass',
            ]);

            return $instance;
        }

        public function testGetCommand()
        {
            $app = $this->getAppMock(function(MockBuilder $builder) {
                $builder->setMethods(['make']);
            });

            $fileMock = $this->getFilesMock();
            $stringCompiler = $this->getStringCompilerMock($fileMock, 'views');

            $blade = $this->getBladeMock(function(MockBuilder $builder){
                $builder->disableOriginalConstructor();
            });

            $blade->expects($this->any())->method('extend');

            $bladed = $this->getBladedMock($app, $blade);

            $app->expects($this->exactly(1))->method('make')->will(
                $this->returnValue($command = $this->getCommandMock($app, 1))
            );

            $instance = $this->getInstance($bladed, $app, $stringCompiler);

            $gotCommand = $instance->getCommand('someCommand', $this->getViewMock());
            $this->assertEquals($command, $gotCommand);


        }


        public function testExtendCommand()
        {
            $app = $this->getAppMock(function(MockBuilder $builder) {
                $builder->setMethods(['make', 'bound']);
            });

            $fileMock = $this->getFilesMock();
            $stringCompiler = $this->getStringCompilerMock($fileMock, 'views');

            $blade = $this->getBladeMock(function(MockBuilder $builder){
                $builder->disableOriginalConstructor();
            });

            $blade->expects($this->any())->method('extend');

            $bladed = $this->getBladedMock($app, $blade);

            $app->expects($this->exactly(1))->method('make')->will(
                $this->returnValue($command = $this->getCommandMock($app, 0))
            );

            $app->expects($this->exactly(1))->method('bound')->will(
                $this->returnValue(true)
            );

            $instance = $this->getInstance($bladed, $app, $stringCompiler);

            $callable = function ()  {
                return 5;
            };

            $instance->extendCommand('someNamespace', 'myExtension', $callable);

            $this->assertEquals($callable(), $command->myExtension());
        }


        public function testExtendCommandFails()
        {
            $app = $this->getAppMock(function(MockBuilder $builder) {
                $builder->setMethods(['make', 'bound']);
            });

            $fileMock = $this->getFilesMock();
            $stringCompiler = $this->getStringCompilerMock($fileMock, 'views');

            $blade = $this->getBladeMock(function(MockBuilder $builder){
                $builder->disableOriginalConstructor();
            });

            $blade->expects($this->any())->method('extend');

            $bladed = $this->getBladedMock($app, $blade);

            $app->expects($this->exactly(1))->method('bound')->will(
                $this->returnValue(false)
            );

            $instance = $this->getInstance($bladed, $app, $stringCompiler);

            $callable = function ()  {
                return 5;
            };


            $this->setExpectedException('Exception');
            $instance->extendCommand('someNamespace', 'myExtension', $callable);
        }

    }
