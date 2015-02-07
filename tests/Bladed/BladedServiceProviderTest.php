<?php
    namespace LaravelCommode\Bladed;

    use Illuminate\Filesystem\Filesystem;
    use Illuminate\View\Compilers\BladeCompiler;
    use LaravelCommode\Bladed\Interfaces\IBladedManager;
    use LaravelCommode\Bladed\Manager\BladedManager;
    use LaravelCommode\Common\GhostService\GhostService;
    use LaravelCommode\Common\GhostService\GhostServices;
    use LaravelCommode\Common\Resolver\Resolver;
    use PHPUnit_Framework_MockObject_MockObject as MockObject;
    use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

    class BladedServiceProviderTest extends \PHPUnit_Framework_TestCase
    {
        protected function getAppMock(\Closure $callable = null)
        {
            if ($callable) {
                $callable($mock = $this->getMockBuilder('\Illuminate\Foundation\Application'));
                return $mock->getMock();
            }

            return $this->getMock('\Illuminate\Foundation\Application');
        }

        protected function getInstance($app)
        {
            return new BladedServiceProvider($app);
        }

        public function testSome()
        {
            $instance = $this->getInstance($app = $this->getAppMock(function (MockBuilder $builder) {
                $builder->setMethods(['bound', 'forceRegister', 'make', 'booting', 'singleton']);
            }));

            $app->expects($this->any(1))->method('make')->will($this->returnCallback(function($ns) use ($app) {

                switch ($ns) {
                    case 'commode.common.ghostservice':
                        return new GhostServices();
                        break;
                    case 'commode.common.resolver':
                        return new Resolver($app);
                        break;
                    case 'commode.bladed':
                        return $this->getMock(BladedManager::class, [], [], '', 0);
                    case 'blade.compiler':
                        return $this->getMock(BladeCompiler::class, [], [new Filesystem(), '']);
                        break;
                    case 'files':
                        return new Filesystem();
                    case 'path.storage':
                        return function ($p) {return "./{$p[0]}";};
                }

            }));


            $app->expects($this->any())->method('singleton')->will($this->returnCallback(function ($ns, $closure) use ($app) {
                $this->assertTrue($closure instanceof \Closure);

                switch($ns)
                {
                    case 'commode.bladed':
                    case IBladedManager::class:
                        $this->assertTrue($closure($app) instanceof BladedManager);
                        break;
                }
            }));

            $reflection = new \ReflectionClass($instance);

            $methodRegistering = $reflection->getMethod('registering');
            $methodLaunching = $reflection->getMethod('launching');

            $methodRegistering->setAccessible(1);
            $methodLaunching->setAccessible(1);

            $methodRegistering->invoke($instance);
            $methodLaunching->invoke($instance);
        }
    }
