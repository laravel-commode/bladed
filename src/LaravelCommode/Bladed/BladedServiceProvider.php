<?php
    namespace LaravelCommode\Bladed {

        use LaravelCommode\Bladed\Compilers\BladedCompiler;
        use LaravelCommode\Bladed\Compilers\StringCompiler;
        use LaravelCommode\Bladed\Interfaces\IBladedManager;
        use LaravelCommode\Bladed\Manager\BladedManager;
        use LaravelCommode\BladeExtender\Compiler\Compiler;

        use LaravelCommode\Common\GhostService\GhostService;

        /**
         * Created by PhpStorm.
         * User: madman
         * Date: 03.02.15
         * Time: 2:25
         */
        class BladedServiceProvider extends GhostService
        {
            protected $aliases = [
                'Bladed' => 'LaravelCommode\Bladed\Manager\BladedFacade'
            ];

            /**
             * Will be triggered when the app's 'booting' event is triggered
             */
            protected function launching()
            {

                $this->with(['commode.bladed'], function(IBladedManager $manager) {
                    $manager->registerCommandNamespace('scope', 'LaravelCommode\Bladed\DefaultCommands\Scope');
                    $manager->registerCommandNamespace('form', 'LaravelCommode\Bladed\DefaultCommands\Form');
                    $manager->registerCommandNamespace('template', 'LaravelCommode\Bladed\DefaultCommands\Template');
                });
            }

            /**
             * Triggered when service is being registered
             */
            protected function registering()
            {
                $this->app->singleton('commode.bladed', function ($app) {
                    $compiler = new BladedCompiler($app->make('blade.compiler'), $app, 'Bladed::getCommand', 'commode.bladed');
                    $stringCompiler = new StringCompiler($app->make('files'), call_user_func($app->make('path.storage'), ['views']));
                    return new BladedManager($compiler, $app, $stringCompiler);
                });

                $this->app->singleton('LaravelCommode\Bladed\BladedManager\Interfaces\IBladedManager', function ($app) {
                    return $app->make('commode.bladed');
                });
            }
        }
    }

    namespace {
        function bladed($command, $environment) {
            return \Bladed::getCommand("commode.bladed.{$command}", $environment);
        }
    }