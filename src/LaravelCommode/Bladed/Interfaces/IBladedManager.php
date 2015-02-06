<?php
    namespace LaravelCommode\Bladed\Interfaces;

    /**
     * Created by PhpStorm.
     * User: madman
     * Date: 03.02.15
     * Time: 18:09
     */
    interface IBladedManager
    {

        /**
         * @param $commandNamespace
         * @param $responsible
         * @return mixed
         */
        public function registerCommandNamespace($commandNamespace, $responsible);

        /**
         * @param array $commandNamespaces
         * @return mixed
         */
        public function registerCommandNamespaces(array $commandNamespaces);

        /**
         * @param $commandNamespace
         * @param null $__env
         * @return IBladedCommand
         */
        public function getCommand($commandNamespace, $__env = null);
    }