<?php

namespace LaravelCommode\Bladed\Compilers;

use Illuminate\Filesystem\Filesystem;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class StringCompilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var StringCompiler
     */
    private $testInstance;

    protected function setUp()
    {
        $this->testInstance = new StringCompiler(new Filesystem(), __DIR__);
        parent::setUp();
    }

    public function testCompile()
    {
        $testArguments = ['dummyVar' => uniqid()];

        $string = $this->testInstance->compileWiths("{{\$dummyVar}}", $testArguments);

        $this->assertSame($testArguments['dummyVar'], $string);
    }

    public function testFail()
    {
        try {
            $string = $this->testInstance->compileWiths("{{\$dummyVar}}", []);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }
    }

    protected function tearDown()
    {
        unset($this->testInstance);
        parent::tearDown();
    }
}
