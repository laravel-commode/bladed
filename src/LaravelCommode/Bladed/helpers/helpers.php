<?php

use LaravelCommode\Bladed\BladedServiceProvider as Service;
use LaravelCommode\Bladed\Compilers\TemplateCompiler;

function bladed($command, $environment = null)
{

    return app(Service::PROVIDES_SERVICE)
        ->getCommand(Service::PROVIDES_SERVICE.".{$command}", $environment ?: app('view'));
}

function bladedTemplate($environment = null)
{
    return new TemplateCompiler(app(Service::PROVIDES_STRING_COMPILER), $environment ?: app('view'));
}
