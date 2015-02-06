<?php
    namespace LaravelCommode\Bladed\Commands;


    use PHPUnit_Framework_MockObject_MockObject as MockObject;
    use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

    class ABladedCommandTest extends \PHPUnit_Framework_TestCase
    {

        /**
         * @param callable $callback
         * @return MockObject
         */
        protected function getAppMock(\Closure $build = null, \Closure $mocking = null)
        {
            $mock = $this->getMockBuilder('\Illuminate\Foundation\Application');

            if ($build !== null) {
                $build($mock);
            }

            $mock = $mock->getMock();

            if ($mocking !== null) {
                $mocking($mock);
            }

            return $mock;
        }

        /**
         * @return MockObject
         */
        protected function getViewMock()
        {
            $mock = $this->getMockBuilder('\Illuminate\View\Factory')->disableOriginalConstructor();
            return $mock->getMock();
        }

        /**
         * @param $appMock
         * @return MockObject|ABladedCommand
         */
        protected function getInstance($appMock)
        {
            return $this->getMockForAbstractClass('LaravelCommode\Bladed\Commands\ABladedCommand', [$appMock]);
        }

        public function testConstructorAndGetApp()
        {
            $mock = $this->getInstance($appMock = $this->getAppMock());

            $this->assertEquals($appMock, $mock->getApplication());
        }

        public function testConstructorAndGetSetEnv()
        {
            $viewMock = $this->getViewMock();

            $appMock = $this->getAppMock(function (MockBuilder $builder) {
                $builder->setMethods(['make']);

            }, function (MockObject $object) use ($viewMock) {
                $object->expects($this->exactly(2))->method('make')->will($this->returnValue($viewMock));
            });

            $mock = $this->getInstance($appMock);

            $this->assertEquals($viewMock, $mock->getEnvironment());

            $mock->setEnvironment(null);
            $this->assertEquals($viewMock, $mock->getEnvironment());

            $mock->setEnvironment($viewMock);
            $this->assertEquals($viewMock, $mock->getEnvironment());
        }

        public function testExtendCall()
        {
            $appMock = $this->getAppMock();
            $mock = $this->getInstance($appMock);


            $returnValue = 5;
            $extendedClosure = function () use ($returnValue) {
                return $returnValue;
            };

            $extendedClosures = function () {
                return $this->method();
            };

            $mock->extend('method', $extendedClosure);
            $this->assertEquals($returnValue, $mock->method());


            $mock->extend('methods', $extendedClosures, 1);
            $this->assertEquals($returnValue, $mock->methods());

            $this->setExpectedException(\BadMethodCallException::class);

            $mock->callFake();

        }
    }
