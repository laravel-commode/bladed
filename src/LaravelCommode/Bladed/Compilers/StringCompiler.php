<?php
namespace LaravelCommode\Bladed\Compilers;

use Illuminate\View\Compilers\BladeCompiler;

class StringCompiler extends BladeCompiler
{

    /**
     * Compile blade template with passing arguments.
     *
     * @param $value
     * @param  array $args
     * @throws \Exception
     * @return string
     */
    public function compileWiths($value, array $args = array())
    {
        $generated = parent::compileString($value);

        ob_start() and extract($args, EXTR_SKIP);

        try {
            eval('?>'.$generated);
        } catch (\Exception $e) {
            ob_get_clean();
            throw $e;
        }

        $content = ob_get_clean();

        return $content;
    }
}
