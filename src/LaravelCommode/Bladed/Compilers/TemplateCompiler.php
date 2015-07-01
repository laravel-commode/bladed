<?php

namespace LaravelCommode\Bladed\Compilers;

use Illuminate\Contracts\View\Factory;

class TemplateCompiler
{
    /**
     * @var StringCompiler
     */
    private $stringCompiler;

    /**
     * @var string
     */
    private $template;

    /**
     * @var mixed[]
     */
    private $arguments = [];

    /**
     * @var Factory
     */
    private $factory;

    public function __construct(StringCompiler $stringCompiler, Factory $factory)
    {
        $this->stringCompiler = $stringCompiler;
        $this->factory = $factory;
    }

    /**
     * @param $template
     * @return string
     */
    public static function composeTemplate($template)
    {
        return addslashes(strtr($template, [
            '$'     => '(:var)',
            '<?php' => '(:<php)',
            '<?='   => '(:<ephp)',
            '?>'    => '(:php>)'
        ]));
    }

    /**
     * @param $template
     * @return string
     */
    public static function compileTemplate($template)
    {
        return strtr(stripslashes($template), [
            '(:var)'    => '$',
            '(:<php)'   => '<?php',
            '(:<ephp)'  => '<?=',
            '(:php>)'   => '?>'
        ]);
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     * @param bool $clarify
     * @return $this
     */
    public function setTemplate($template, $clarify = true)
    {
        if ($clarify) {
            $template = self::compileTemplate($template);
        }

        $this->template = $template;

        return $this;

    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @param array $arguments
     * @return $this
     */
    public function appendArguments(array $arguments)
    {
        $this->arguments= array_merge($this->arguments, $arguments);
        return $this;
    }

    /**
     * @param array $values
     * @return string
     * @throws \Exception
     */
    public function render(array $values = [])
    {
        try {
            $result = $this->stringCompiler->compileWiths(
                $this->getTemplate(),
                array_merge($this->arguments, $values, $this->factory->getShared())
            );
        } catch (\Exception $e) {
            throw $e;
        }

        return $result;
    }

    public function __toString()
    {
        return $this->render();
    }
}
