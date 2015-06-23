<?php

namespace LaravelCommode\Bladed\Compilers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class TemplateCompilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateCompiler
     */
    private $testInstance;

    /**
     * @var StringCompiler
     */
    private $stringCompiler;

    /**
     * @var Factory|Mock
     */
    private $viewMock;

    protected function setUp()
    {
        $this->stringCompiler = new StringCompiler(new Filesystem(), __DIR__);

        $this->viewMock = $this->getMock(
            Factory::class,
            ['exists', 'file', 'make', 'share', 'composer', 'creator', 'addNamespace', 'getShared'],
            [],
            '',
            false
        );

        $this->testInstance = new TemplateCompiler($this->stringCompiler, $this->viewMock);

        parent::setUp();
    }

    private function composeTemplate()
    {
        return TemplateCompiler::composeTemplate("<?php \$a = isset(\$a) ? \$a : 5;?>{{\$a}}");
    }

    public function testRender()
    {
        $template = $this->composeTemplate();

        $this->testInstance->setTemplate($template);

        $this->viewMock->expects($this->any())->method('getShared')
            ->will($this->returnValue([]));

        $this->assertSame($this->testInstance->render(), (string) $this->testInstance);
        $this->assertSame('6', $this->testInstance->render(['a' => 6]));

        $this->testInstance->setTemplate($this->composeTemplate()."{{\$b}}");

        try {
            $this->testInstance->render();
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }
    }

    public function testArguments()
    {
        $this->testInstance->setArguments(['a' => 5]);
        $this->testInstance->appendArguments(['b' => 6]);
        $this->assertSame(['a' => 5, 'b' => 6], $this->testInstance->getArguments());
    }

    protected function tearDown()
    {
        unset($this->testInstance, $this->viewMock, $this->stringCompiler);
        parent::tearDown();
    }
}
