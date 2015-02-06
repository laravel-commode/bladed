<?php
    namespace LaravelCommode\Bladed\Interfaces;

    use Illuminate\View\Factory;

    interface IBladedCommand
    {
        /**
         * @param Factory $factory
         * @return $this
         */
        public function setEnvironment(Factory $factory = null);

        /**
         * @return Factory
         */
        public function getEnvironment();

        public function extend($methodName, \Closure $callable, $rebindScope = false);
    }