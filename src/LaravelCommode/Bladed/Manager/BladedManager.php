<?php

namespace LaravelCommode\Bladed\Manager;

use Illuminate\Contracts\Foundation\Application;

use Illuminate\Contracts\View\Factory;
use LaravelCommode\Bladed\Compilers\BladedCompiler;
use LaravelCommode\Bladed\Compilers\StringCompiler;

use LaravelCommode\Bladed\Interfaces\IBladedCommand;
use LaravelCommode\Bladed\Interfaces\IBladedManager;

use UnexpectedValueException;

class BladedManager implements IBladedManager
{
    /**
     * @var BladedCompiler
     */
    private $bladed;

    /**
     * @var Application
     */
    private $application;

    private $environmental = [];

    /**
     * @var StringCompiler
     */
    private $stringCompiler;

    public function __construct(BladedCompiler $bladed, Application $application, StringCompiler $stringCompiler)
    {
        $this->bladed = $bladed;
        $this->application = $application;
        $this->stringCompiler = $stringCompiler;
    }

    /**
     * Registers command namespace.
     *
     * @param string $commandNamespace Command namespace name
     * @param string $bladedCommand Command namespace class
     * @return $this
     */
    public function registerCommandNamespace($commandNamespace, $bladedCommand)
    {
        $this->bladed->registerNamespace($commandNamespace, $bladedCommand);
        return $this;
    }

    /**
     * Registers multiple command namespace.
     * Requires an array of ['commandNamespaceName' => BladedCommand::class].
     *
     * @param array $commandNamespaces
     * @return $this
     */
    public function registerCommandNamespaces(array $commandNamespaces)
    {
        foreach ($commandNamespaces as $command => $responsible) {
            $this->registerCommandNamespace($commandNamespaces, $responsible);
        }

        return $this;
    }

    /**
     * Returns IBladedCommand instance by provided namespace name.
     * Injects view environment(Factory) if it's not null.
     *
     * @param $commandNamespace
     * @param Factory|null $environment
     * @return IBladedCommand
     */
    public function getCommand($commandNamespace, Factory $environment = null)
    {
        $commandName = $this->testCommandRegistration($commandNamespace);

        /**
         * @var IBladedCommand $command
         */
        $command = $this->application->make($commandName);

        if (($environment !== null) && (!in_array($commandNamespace, $this->environmental, true))) {
            $this->environmental[] = $commandNamespace;
            $command->setEnvironment($environment);
        }

        return $command;
    }

    /* @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Extends command namespace by adding $callable under
     * $methodName.
     * You can bind $callable to command scope by passing
     * $rebindScope as true value.
     *
     * @param $commandNamespace
     * @param $methodName
     * @param callable $callable
     * @param bool $rebindScope
     * @return mixed
     */
    public function extendCommand($commandNamespace, $methodName, \Closure $callable, $rebindScope = false)
    {
        $commandName = $this->testCommandRegistration($commandNamespace);

        /**
         * @var IBladedCommand $command
         */
        $command = $this->application->make($commandName);

        $command->extend($methodName, $callable, $rebindScope);

        return $this;
    }

    /**
     * @param $commandNamespace
     * @return string
     * @throws \Exception
     */
    private function testCommandRegistration($commandNamespace)
    {
        $commandName = implode('.', [$this->bladed->getIoCRegistry(), $commandNamespace]);

        if (!$this->application->bound($commandName)) {
            throw new UnexpectedValueException("Unable to resolve \"{$commandNamespace}\" command stack.");
        }

        return $commandName;
    }
}
