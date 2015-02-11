<?php
    namespace LaravelCommode\Bladed\Manager;

    use Illuminate\Support\Facades\Facade;

    class BladedFacadeTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @param array $methods
         * @return \PHPUnit_Framework_MockObject_MockObject|Facade
         */
        protected function getInstanceMock(array $methods = [])
        {
            return new BladedFacade();
        }

        public function testAccessor()
        {
            $instance = $this->getInstanceMock();

            $reflection = new \ReflectionClass($instance);
            $methodReflection = $reflection->getMethod('getFacadeAccessor');
            $methodReflection->setAccessible(true);
            $this->assertSame('commode.bladed', $methodReflection->invoke($instance));

        }
    }
