<?php
    namespace LaravelCommode\Bladed\Manager;
    use Illuminate\Foundation\Application;
    use Illuminate\Support\Facades\Facade;
    use LaravelCommode\Bladed\Compilers\BladedCompiler;
    use LaravelCommode\Bladed\Interfaces\IBladedCommand;
    use LaravelCommode\Bladed\Interfaces\IBladedManager;

    /**
     * Created by PhpStorm.
     * User: madman
     * Date: 03.02.15
     * Time: 18:12
     */
    class BladedFacade extends Facade
    {
        protected static function getFacadeAccessor()
        {
            return 'commode.bladed';
        }
    }