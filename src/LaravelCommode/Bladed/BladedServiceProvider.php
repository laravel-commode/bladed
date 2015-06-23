<?php

namespace LaravelCommode\Bladed;

use Illuminate\Contracts\Foundation\Application;

use Illuminate\Html\HtmlServiceProvider;
use LaravelCommode\Bladed\Compilers\BladedCompiler;
use LaravelCommode\Bladed\Compilers\StringCompiler;

use LaravelCommode\Bladed\DefaultCommands\Form;
use LaravelCommode\Bladed\DefaultCommands\Scope;
use LaravelCommode\Bladed\DefaultCommands\Template;

use LaravelCommode\Bladed\Interfaces\IBladedManager;

use LaravelCommode\Bladed\Manager\BladedFacade;
use LaravelCommode\Bladed\Manager\BladedManager;

use LaravelCommode\SilentService\SilentService;

class BladedServiceProvider extends SilentService
{
    const PROVIDES_SERVICE = 'laravel-commode.bladed';
    const PROVIDES_STRING_COMPILER = 'laravel-commode.bladed.string-compiler';

    protected function uses()
    {
        return [
            HtmlServiceProvider::class
        ];
    }

    protected function aliases()
    {
        return [ 'Bladed' => BladedFacade::class ];
    }

    public function provides()
    {
        return [self::PROVIDES_SERVICE, self::PROVIDES_STRING_COMPILER, IBladedManager::class];
    }

    /**
     * Will be triggered when the app's 'booting' event is triggered
     */
    public function launching()
    {
        /**
         * registering default commands
         */
        $this->with([self::PROVIDES_SERVICE], function (IBladedManager $manager) {
            $manager->registerCommandNamespace('scope', Scope::class);
            \phpQuery::newDocument();
            $manager->registerCommandNamespace('form', Form::class);
            $manager->registerCommandNamespace('template', Template::class);
        });
    }

    /**
     * Triggered when service is being registered
     */
    public function registering()
    {
        require_once 'helpers/helpers.php';

        $this->app->singleton(self::PROVIDES_STRING_COMPILER, function (Application $app) {
            return new StringCompiler($app->make('files'), storage_path('views'));
        });

        $this->app->singleton(self::PROVIDES_SERVICE, function (Application $app) {

            $compiler = new BladedCompiler(
                $app->make('blade.compiler'),
                $app,
                'Bladed::getCommand',
                self::PROVIDES_SERVICE
            );

            return new BladedManager($compiler, $app, $app->make(self::PROVIDES_STRING_COMPILER));
        });

        $this->app->singleton(IBladedManager::class, function (Application $app) {
            return $app->make(self::PROVIDES_SERVICE);
        });
    }
}
