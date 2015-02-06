<?php
    namespace LaravelCommode\Bladed\Manager;
    use Illuminate\Foundation\Application;
    use LaravelCommode\Bladed\Compilers\BladedCompiler;
    use LaravelCommode\Bladed\Compilers\StringCompiler;
    use LaravelCommode\Bladed\Interfaces\IBladedCommand;
    use LaravelCommode\Bladed\Interfaces\IBladedManager;

    /**
     * Created by PhpStorm.
     * User: madman
     * Date: 03.02.15
     * Time: 18:12
     */
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
         * @param $commandNamespace
         * @param $bladedCommand
         * @return mixed
         */
        public function registerCommandNamespace($commandNamespace, $bladedCommand)
        {
            $this->bladed->registerNamespace($commandNamespace, $bladedCommand);
            return $this;
        }

        /**
         * @param array $commandNamespaces
         * @return mixed
         */
        public function registerCommandNamespaces(array $commandNamespaces)
        {
            foreach($commandNamespaces as $command => $responsible) {
                $this->registerCommandNamespace($commandNamespaces, $responsible);
            }

            return $this;
        }

        /**
         * @param $commandNamespace
         * @param null $__env
         * @return IBladedCommand
         */
        public function getCommand($commandNamespace, $__env = null)
        {
            /**
             * @var IBladedCommand $command
             */
            $command = $this->application->make("{$this->bladed->getIoCRegistry()}.{$commandNamespace}");

            if (($__env !== null) && (!in_array($commandNamespace, $this->environmental))) {
                $this->environmental[] = $commandNamespace;
                $command->setEnvironment($__env);
            }

            return $command;
        }

        /**
         * @param $commandNamespace
         * @param $methodName
         * @param callable $callable
         * @param bool $rebindScope
         * @return IBladedCommand
         * @throws \Exception
         */
        public function extendCommand($commandNamespace, $methodName, \Closure $callable, $rebindScope = false)
        {
            if (!$this->application->bound($commandName = "{$this->bladed->getIoCRegistry()}.{$commandNamespace}")) {
                throw new \Exception("Unable to resolve \"{$commandNamespace}\" command stack.");
            }

            /**
             * @var IBladedCommand $command
             */
            $command = $this->application->make($commandName);

            $command->extend($methodName, $callable, $rebindScope);

            return $this;
        }

        public function getStringCompiler()
        {
            return $this->stringCompiler;
        }
    }