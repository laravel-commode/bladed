<?php

namespace LaravelCommode\Bladed\Manager;

use Illuminate\Support\Facades\Facade;
use LaravelCommode\Bladed\BladedServiceProvider;

class BladedFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BladedServiceProvider::PROVIDES_SERVICE;
    }
}
