<?php
namespace LaravelCommode\Bladed\Commands;

use BadMethodCallException;
use Exception;

abstract class ADelegateBladedCommand extends ABladedCommand
{
    abstract public function getDelegate();

    public function __call($method, array $arguments = [])
    {
        $delegate = $this->getDelegate();

        if (is_callable([$delegate, $method])) {
            return call_user_func_array([$delegate, $method], $arguments);
        }

        return parent::__call($method, $arguments);

    }
}
