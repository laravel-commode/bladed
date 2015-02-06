<?php
    namespace LaravelCommode\Bladed\Commands;
    use Illuminate\Foundation\Application;
    use Illuminate\View\Factory;
    use LaravelCommode\Bladed\Interfaces\IBladedCommand;

    /**
     * Created by PhpStorm.
     * User: madman
     * Date: 03.02.15
     * Time: 19:57
     */
    abstract class ADelegateBladedCommand extends ABladedCommand
    {
        private $delegeteTo;

        abstract public function getDelegate();

        /**
         * @param $method
         * @param array $arguments
         * @return mixed
         */
        public function __call($method, array $arguments = [])
        {
            try {
                $res = parent::__call($method, $arguments);
            } catch(\BadMethodCallException $e) {
                try {
                    $this->delegeteTo = $this->delegeteTo !== null ?: $this->getDelegate();
                    $res = call_user_func_array([$this->delegeteTo, $method], $arguments);
                } catch(\Exception $ex) {
                    throw $e;
                }

            }

            return $res;
        }
    }