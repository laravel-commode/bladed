<?php

use Illuminate\Container\Container;

define('BL_TEST_DIR', realpath(__DIR__."/../"));

function bl_path($path = '')
{
    return BL_TEST_DIR . ($path === '' ? '' : '/'.$path);
}

function bl_get_contents($path = '')
{
    return file_get_contents(bl_path($path));
}

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return app('path.storage').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (!function_exists('app')) {
    function app($make = null, array $parameters = [])
    {
        if (null === $make) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make, $parameters);
    }
}

if (!function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param  string  $id
     * @param  array   $parameters
     * @param  string  $domain
     * @param  string  $locale
     * @return string
     */
    function trans($id = null, $parameters = [], $domain = 'messages', $locale = null)
    {
        return app('translator')->trans($id, $parameters, $domain, $locale);
    }
}
