<?php

namespace LaravelCommode\Bladed\DefaultCommands;

use LaravelCommode\Bladed\Commands\ABladedCommand;

class Scope extends ABladedCommand
{
    public function set(&$var, $value)
    {
        $var = $value;
    }

    public function setIf(&$var, $value)
    {
        if ($var === null) {
            $var = $value;
        }
    }

    public function share($key, $value)
    {
        $this->getEnvironment()->share($key, $value);
    }

    public function l($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return trans($id, $parameters, $domain, $locale);
    }

    public function dd()
    {
        return call_user_func_array('dd', func_get_args());
    }

    /**
     * @return mixed
     */
    public function dump()
    {
        return call_user_func_array('var_dump', func_get_args());
    }
}