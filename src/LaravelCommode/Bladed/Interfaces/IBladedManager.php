<?php

namespace LaravelCommode\Bladed\Interfaces;

use Illuminate\Contracts\View\Factory;

interface IBladedManager
{

    /**
     * Registers command namespace.
     *
     * @param string $commandNamespace Command namespace name
     * @param string $bladedCommand Command namespace class
     * @return $this
     */
    public function registerCommandNamespace($commandNamespace, $bladedCommand);

    /**
     * Registers multiple command namespace.
     * Requires an array of ['commandNamespaceName' => BladedCommand::class].
     *
     * @param array $commandNamespaces
     * @return $this
     */
    public function registerCommandNamespaces(array $commandNamespaces);

    /**
     * Returns IBladedCommand instance by provided namespace name.
     * Injects view environment(Factory) if it's not null.
     *
     * @param $commandNamespace
     * @param Factory|null $environment
     * @return IBladedCommand
     */
    public function getCommand($commandNamespace, Factory $environment = null);

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
    public function extendCommand($commandNamespace, $methodName, \Closure $callable, $rebindScope = false);
}
