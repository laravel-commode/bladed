<?php

namespace LaravelCommode\Bladed;

use Illuminate\Container\Container;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;

use LaravelCommode\Bladed\Compilers\StringCompiler;
use LaravelCommode\Bladed\Interfaces\IBladedManager;
use LaravelCommode\Bladed\Manager\BladedFacade;
use LaravelCommode\Bladed\Manager\BladedManager;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use PHPUnit_Framework_TestCase;

class BladedServiceProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Application|Mock|ContainerContract
     */
    private $appMock;

    /**
     * @var BladedServiceProvider
     */
    private $testInstance;

    /**
     * @var BladeCompiler|Mock
     */
    private $bladeCompilerMock;

    /**
     * @var BladedManager|Mock
     */
    private $bladedManagerMock;


    protected function setUp()
    {
        $contractMethods = [
            'bound', 'alias', 'tag', 'tagged', 'bind', 'bindIf', 'singleton',
            'extend','instance','when','make','call','resolved','resolving',
            'afterResolving'
        ];

        $appMethods = [
            'version', 'basePath', 'environment', 'isDownForMaintenance',
            'registerConfiguredProviders', 'register', 'registerDeferredProvider',
            'boot', 'booting', 'booted', 'getCachedCompilePath', 'getCachedServicesPath'
        ];

        $this->appMock = $this->getMock(
            'Illuminate\Contracts\Foundation\Application',
            array_merge($contractMethods, $appMethods)
        );

        $this->bladeCompilerMock = $this->getMock(BladeCompiler::class, [], [], '', false);

        $this->bladedManagerMock = $this->getMock(BladedManager::class, [], [], '', false);

        $this->testInstance = new BladedServiceProvider($this->appMock);

        Container::setInstance($this->appMock);

        parent::setUp();
    }

    public function testRegistering()
    {

        $this->appMock->expects($this->at(0))->method('singleton')
            ->will($this->returnCallback(function ($boundTo, $closure) {
                switch($boundTo)
                {
                    case BladedServiceProvider::PROVIDES_SERVICE:
                        $this->assertTrue($closure($this->appMock) instanceof BladedManager);
                        break;
                    case IBladedManager::class:
                        $this->assertTrue($closure($this->appMock) instanceof BladedManager);
                        break;
                    case BladedServiceProvider::PROVIDES_STRING_COMPILER:
                        $this->assertTrue($closure($this->appMock) instanceof StringCompiler);
                        break;
                    default:
                        var_dump($boundTo);
                        die('dead: ' . debug_print_backtrace());
                }

            }));


        $this->appMock->expects($this->any())->method('make')
            ->will($this->returnCallback(function ($resolvable) {
                switch ($resolvable)
                {
                    case 'files':
                        return new Filesystem();
                    case 'blade.compiler':
                        return $this->bladeCompilerMock;
                    case 'path.storage':
                        return __DIR__;
                    default:
                        var_dump($resolvable);
                        die('dead');
                }
            }));

        $this->assertNull($this->testInstance->registering());
    }

    public function testLaunching()
    {
        $this->bladedManagerMock->expects($this->exactly(3))->method('registerCommandNamespace');

        $this->appMock->expects($this->any())->method('make')
            ->will($this->returnCallback(function ($resolvable) {
                switch($resolvable)
                {
                    case BladedServiceProvider::PROVIDES_SERVICE:
                        return $this->bladedManagerMock;
                }
            }));

        $this->testInstance->launching();
    }

    public function testUses()
    {
        $reflectionMethod = new \ReflectionMethod(BladedServiceProvider::class, 'uses');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->testInstance);
        $reflectionMethod->setAccessible(false);
    }

    public function testAliases()
    {
        $reflectionMethod = new \ReflectionMethod($this->testInstance, 'aliases');
        $reflectionMethod->setAccessible(true);
        $this->assertSame(['Bladed' => BladedFacade::class], $reflectionMethod->invoke($this->testInstance));
        $reflectionMethod->setAccessible(false);
    }

    public function testProvides()
    {
        $this->assertSame(
            [
                BladedServiceProvider::PROVIDES_SERVICE,
                BladedServiceProvider::PROVIDES_STRING_COMPILER,
                IBladedManager::class
            ],
            $this->testInstance->provides()
        );
    }

    protected function tearDown()
    {
        $reflectionProperty = new \ReflectionProperty(Container::class, 'instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, null);
        $reflectionProperty->setAccessible(false);

        unset($this->testInstance, $this->appMock);
        parent::tearDown();
    }
}
