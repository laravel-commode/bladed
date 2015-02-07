<?php
    namespace LaravelCommode\Bladed\Compilers;
    use Illuminate\View\Factory;

    /**
     * Created by PhpStorm.
     * User: madman
     * Date: 03.02.15
     * Time: 22:48
     */

    class TemplateCompiler
    {
        /**
         * @var StringCompiler
         */
        private $stringCompiler;

        private $template;

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
         * @return mixed
         */
        public function getTemplate($clerify = true)
        {

            $template = $this->template;

            if ($clerify) {
                $template = str_replace("(:<php)", '<?php', $template);
                $template = str_replace("(:<ephp)", '<?=', $template);
                $template = str_replace("(:php>)", '?>', $template);
                $template = str_replace("(:var)", '$', $template);
                $template = stripcslashes($template);
            }

            return $template;
        }

        /**
         * @param mixed $template
         * @return $this
         */
        public function setTemplate($template)
        {
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
        public function setArguments($arguments)
        {
            $this->arguments = $arguments;
            return $this;
        }

        /**
         * @param array $arguments
         * @return $this
         */
        public function appendArguments($arguments)
        {
            $this->arguments= array_merge($this->arguments, $arguments);
            return $this;
        }

        public function render($values = [])
        {
            try {
                $result = $this->stringCompiler->compileWiths(
                    $this->getTemplate(),
                    array_merge($this->arguments, $values, $this->factory->getShared())
                );
            } catch(\Exception $e) {
                throw $e;
            }

            return $result;
        }

        public function __toString()
        {
            return $this->render();
        }
    }