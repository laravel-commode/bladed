<?php

namespace LaravelCommode\Bladed\Compilers;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;

use LaravelCommode\Bladed\BladedServiceProvider;
use LaravelCommode\Bladed\Manager\BladedManager;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class BladedCompilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BladedCompiler
     */
    private $testInstance;

    /**
     * @var Application|Mock
     */
    private $appMock;

    /**
     * @var BladeCompiler
     */
    private $bladeCompiler;

    /**
     * @var BladedManager|Mock
     */
    private $bladedManager;

    /**
     * @var Factory|Mock
     */
    private $viewMock;

    /**
     * @var StringCompiler
     */
    private $stringCompiler;

    public $propertyToTest  = 'propertyToTestValue';



    public function methodToTest()
    {
        return $this->propertyToTest;
    }


    protected function setUp()
    {
        $this->bladeCompiler = new BladeCompiler(new Filesystem(), __DIR__);

        $this->viewMock = $this->getMock(
            Factory::class,
            ['exists', 'file', 'make', 'share', 'composer', 'creator', 'addNamespace', 'getShared'],
            [],
            '',
            false
        );

        $this->appMock = $this->getMock(Application::class, []);

        $this->bladedManager = $this->getMock(BladedManager::class, [], [], '', false);

        $this->testInstance = new BladedCompiler($this->bladeCompiler, $this->appMock);

        $this->stringCompiler = new StringCompiler(new Filesystem(), __DIR__);

        Container::setInstance($this->appMock);

        parent::setUp();
    }

    public function testStatements()
    {

        $this->bladedManager->expects($this->any())->method('getCommand')
            ->will($this->returnValue($this));

        $this->appMock->expects($this->any())->method('make')
            ->will($this->returnCallback(function ($resolvable) {
                switch ($resolvable)
                {
                    case 'view':
                        return $this->viewMock;
                    case 'laravel-commode.bladed':
                        return $this->bladedManager;
                    case BladedServiceProvider::PROVIDES_STRING_COMPILER:
                        return $this->stringCompiler;
                    default:
                        var_dump($resolvable, 'in testStatements');
                        die;
                }
            }));

        foreach (['condition', 'statement', 'templates'] as $file) {
            $this->assertSame(
                bl_get_contents('templates/results'.ucfirst($file)),
                $this->bladeCompiler->compileString(bl_get_contents("templates/{$file}.blade._php"))
            );
        }

        $expect = bl_get_contents('templates/resultsIterators');
        $compiled = $this->bladeCompiler->compileString(bl_get_contents("templates/iterators.blade._php"));

        preg_match_all('/(\$count[\d\w]{1,})/is', $compiled, $countVars);
        $countVars = array_values($countVars[0]);

        preg_match_all('/(\$key[\d\w]{1,})/is', $compiled, $keyVars);
        $keyVars = array_values($keyVars[0]);

        $countIteration = 0;
        $keyIteration = 0;

        $expect = preg_replace_callback('/\{\{countVar\}\}/is', function ($match) use ($countVars, &$countIteration) {
            return $countVars[$countIteration++];
        }, $expect);

        $expect = preg_replace_callback('/\{\{\$key\}\}/is', function ($match) use ($keyVars, &$keyIteration) {
            return $keyVars[$keyIteration++];
        }, $expect);

        $this->assertSame($expect, $compiled);

    }

    public function testGettersSetters()
    {
        $this->assertSame(BladedServiceProvider::PROVIDES_SERVICE, $this->testInstance->getIoCRegistry());
        $this->assertSame($this->testInstance, $this->testInstance->setIoCRegistry($ioc = uniqid('ioc')));
        $this->assertSame($ioc, $this->testInstance->getIoCRegistry());
        $this->assertSame(
            $this->testInstance,
            $this->testInstance->setIoCRegistry(BladedServiceProvider::PROVIDES_SERVICE)
        );

        $this->assertSame('bladed', $this->testInstance->getRegistryFunction());
        $this->assertSame($this->testInstance, $this->testInstance->setRegistryFunction($registry = uniqid('registry')));
        $this->assertSame($registry, $this->testInstance->getRegistryFunction());
        $this->assertSame(
            $this->testInstance,
            $this->testInstance->setRegistryFunction('bladed')
        );
    }

    public function testRegistration()
    {
        $test = [
            'command' => '\stdClass',
            'command1' => '\stdClass'
        ];

        $this->appMock->expects($this->any())->method('singleton')
            ->will($this->returnCallback(function ($command, $callback) use ($test) {
                $commandName = str_replace($this->testInstance->getIoCRegistry().".", '', $command);
                $this->assertArrayHasKey($commandName, $test);
                $this->assertTrue($callback($this->appMock) instanceof $test[$commandName]);

            }));

        $this->testInstance->registerNamespaces($test);

        $this->appMock->expects($this->any())->method('make')
            ->will($this->returnValue($this));

        $this->assertSame($this, $this->testInstance->getNamespace('command'));

        try {
            $this->testInstance->getNamespace($command = uniqid('not_existent'));
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \UnexpectedValueException);
            $this->assertSame("Unknown blade command namespace - {$command}.", $e->getMessage());
        }
    }

    /**
     * @param TemplateCompiler $compiler
     * @param mixed $arguments
     */
    public function templateMethodTest($compiler, $arguments)
    {
        $this->assertTrue($compiler instanceof TemplateCompiler);
        $compiler->setArguments($arguments);
        $this->viewMock->expects($this->any())->method('getShared')
            ->will($this->returnValue([]));

        $this->assertSame("    <div>dummy template name</div>\n", $compiler->render());
        return $this->propertyToTest;

    }

    protected function tearDown()
    {
        $containerReflection = new \ReflectionProperty(Container::class, 'instance');

        $containerReflection->setAccessible(true);
        $containerReflection->setValue(null);
        $containerReflection->setAccessible(false);

        unset($this->bladeCompiler, $this->appMock, $this->testInstance);
        parent::tearDown();
    }
}
