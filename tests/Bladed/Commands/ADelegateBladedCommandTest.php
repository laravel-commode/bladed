<?php
    namespace LaravelCommode\Bladed\Commands;

    use PHPUnit_Framework_MockObject_MockObject as MockObject;
    use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

    class ADelegateBladedCommandTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @param callable $build
         * @param callable $mocking
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
         * @param $app
         * @return MockObject|ADelegateBladedCommand
         */
        protected function getInstance($app)
        {
            $abstractMock = $this->getMockForAbstractClass(
                'LaravelCommode\Bladed\Commands\ADelegateBladedCommand', [$app]
            );

            $abstractMock->expects($this->exactly(1))->method('getDelegate')->will($this->returnValue($this));

            return $abstractMock;
        }

        public function testDelegate()
        {
            $instance = $this->getInstance($app = $this->getAppMock());

            $this->assertEquals($this->testing(), $instance->testing());

            $this->setExpectedException(\BadMethodCallException::class);

            $instance->__call('testings');
        }

        public function testing()
        {
            return 'testing';
        }
    }
