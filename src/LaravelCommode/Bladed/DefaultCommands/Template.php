<?php
    namespace LaravelCommode\Bladed\DefaultCommands;
    use LaravelCommode\Bladed\Commands\ABladedCommand;
    use LaravelCommode\Bladed\Commands\TemplateCommand;
    use LaravelCommode\Bladed\Compilers\TemplateCompiler;

    /**
 * Created by PhpStorm.
 * User: madman
 * Date: 03.02.15
 * Time: 23:16
 */
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
                $this->getEnvironment()->make($source)->__toString();
            }

            return $this->templates[$name]->render($arguments);
        }

        public function load($name)
        {
            if (!in_array($name, $this->loadedViews)) {
                $this->getEnvironment()->make($name)->render();
                $this->loadedViews[] = $name;
            }
        }
    }