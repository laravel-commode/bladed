<?php

namespace LaravelCommode\Bladed\Manager;

use LaravelCommode\Bladed\BladedServiceProvider;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class BladedFacadeTest extends PHPUnit_Framework_TestCase
{
    public function testAccessor()
    {
        $reflectionMethod = new \ReflectionMethod(BladedFacade::class, 'getFacadeAccessor');
        $reflectionMethod->setAccessible(true);
        $this->assertSame(BladedServiceProvider::PROVIDES_SERVICE, $reflectionMethod->invoke(null));
        $reflectionMethod->setAccessible(false);
    }
}
