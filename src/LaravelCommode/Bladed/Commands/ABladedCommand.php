<?php
    namespace LaravelCommode\Bladed\Commands;
    use BadMethodCallException;
    use Illuminate\Foundation\Application;
    use Illuminate\View\Factory;
    use LaravelCommode\Bladed\Interfaces\IBladedCommand;

    /**
     * Created by PhpStorm.
     * User: madman
     * Date: 03.02.15
     * Time: 19:57
     */
    abstract class ABladedCommand implements IBladedCommand
    {

        /**
         * @var \Illuminate\Foundation\Application
         */
        private $application;

        /**
         * @var Factory
         */
        private $environment;

        private $extensions = [];

        public function __construct(Application $application)
        {
            $this->application = $application;
        }

        /**
         * @return Application
         */
        public function getApplication()
        {
            return $this->application;
        }
        /**
         * @param Factory $factory
         * @return $this
         */
        public function setEnvironment(Factory $factory = null)
        {
            $this->environment = $factory;
            return $this;
        }

        /**
         * @return Factory
         */
        public function getEnvironment()
        {
            return $this->environment = $this->environment  ?: $this->application->make('view');
        }

        public function extend($methodName, \Closure $callable, $rebindScope = false)
        {
            if ($rebindScope) {
                $callable = $callable->bindTo($this);
            }

            $this->extensions[$methodName] = $callable;

            return $this;
        }

        /**
         * @param $method
         * @param array $arguments
         * @return mixed
         */
        public function __call($method, array $arguments = [])
        {
            if (array_key_exists($method, $this->extensions)) {
                return call_user_func_array($this->extensions[$method], array_merge([$this], $arguments));
            }

            throw new BadMethodCallException();
        }
    }