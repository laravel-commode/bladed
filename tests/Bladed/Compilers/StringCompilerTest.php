<?php
    namespace LaravelCommode\Bladed\Compilers;


    use Illuminate\Filesystem\Filesystem;
    use LaravelCommode\Bladed\Compilers\StringCompiler;

    class StringCompilerTest extends \PHPUnit_Framework_TestCase
    {
        public function getInstance()
        {
            return new StringCompiler(new Filesystem(), '');
        }

        public function testMethod()
        {
            $instance = $this->getInstance();
            $this->assertEquals('Hello, me!', $instance->compileWiths("Hello, {{\$name}}!", ['name' => 'me']));
            /**
            $this->setExpectedException('Exception');
            $instance->compileWiths("Hello, {{\$name}} {{[]}}!", ['name' => 'me']);*/
        }
    }
