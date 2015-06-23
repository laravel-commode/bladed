<?php
namespace LaravelCommode\Bladed\DefaultCommands;

use LaravelCommode\Bladed\Commands\ABladedCommand;
use LaravelCommode\Bladed\Compilers\TemplateCompiler;

class Template extends ABladedCommand
{
    /**
     * @var TemplateCompiler[]
     */
    private $templates = [];

    private $loadedViews = [];

    public function straightRender(TemplateCompiler $compiler, array $arguments = [])
    {
        return $compiler->render($arguments);
    }

    public function add(TemplateCompiler $compiler, $name, array $arguments = [])
    {
        $this->templates[$name] = $compiler->setArguments($arguments);
    }

    public function render($name, array $arguments = [])
    {
        return $this->templates[$name]->render($arguments);
    }

    public function renderOnce($name, array $arguments = [], $source = null)
    {
        if ($source !== null) {
            $this->load($source);
        }

        return $this->templates[$name]->render($arguments);
    }

    public function load($name)
    {
        if (!in_array($name, $this->loadedViews, true)) {
            $this->getEnvironment()->make($name)->render();
            $this->loadedViews[] = $name;
        }
    }
}
