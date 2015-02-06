<?php
    namespace LaravelCommode\Bladed\Compilers;

    use LaravelCommode\Bladed\Interfaces\IBladedCommand;
    use Exception;
    use Illuminate\Foundation\Application;
    use Illuminate\View\Compilers\BladeCompiler;

    class BladedCompiler
    {
        /**
         * @var BladeCompiler
         */
        private $blade;

        /**
         * @var IBladedCommand[]
         */
        private $namespaces = [];
        /**
         * @var \Illuminate\Foundation\Application
         */
        private $application;

        /**
         * call syntax
         * @namespace.method(?$args) @>
         * @namespace.property @>
         */
        const State = '/(@)([a-zA-Z]{1,})(\.)([a-zA-Z]{1,})(.*?)(\ {0,})(\@\>)/is';

        /**
         * call syntax
         * @::@namespace.method(?$args) @>
         * @::@namespace.property @>
         */
        const CacheState = '/(@\:\:)([a-zA-Z]{1,})(\.)([a-zA-Z]{1,})(.*?)(\ {0,})(\@\>)/is';

        /**
         * if syntax
         * @?namespace.method(?$args) ?@>
         * @?namespace.property ?@>
         */
        const Quest = '/(\@\?)([a-zA-Z]{1,})(\.)([a-zA-Z]{1,})(.*?)(\ {0,})(\?\@\>)/is';

        /**
         * unless syntax
         * @!?namespace.method(?$args) ?@>
         * @!?namespace.property ?@>
         */
        const QuestFalse = '/(\@\!\?)([a-zA-Z]{1,})(\.)([a-zA-Z]{1,})(.*?)(\ {0,})(\?\@\>)/is';

        /**
         * template syntax
         * @|namespace.method {
         *     multistring template blade text
         * }|(T_STRING, ?$args[])@>
         */
        const Template = '/(\@\|)([a-z]{1,})(\.)([a-z]{1,})(\ {0,})(\{)(.*?)(\ {0,})(\})(\ {0,})(\|)(\()(.*?)(\))(\@)(\>)/is';

        /**
         * template syntax
         * @|namespace.method {
         *     multistring template blade text
         * }|(T_STRING, ?$args[])@>
         */
        const CacheTemplate = '/(\@\:\:\|)([a-z]{1,})(\.)([a-z]{1,})(\ {0,})(\{)(.*?)(\ {0,})(\})(\ {0,})(\|)(\()(.*?)(\))(\@)(\>)/is';

        /**
         * foreach syntax
         * @in($collection||$value)
         * @in($collection||$key||$value)
         */
        const Each = '/(\@(in|up|down)\()([\$\[\]\:\.\w\d\(\)\_\>\'\"\,\ \-]{1,})(\|{2})(\$[\w]{1,})(((\|{2})(\$[\w]{1,})){0,1})(\))/';
        const EndEach = '/(\@(in|up|down)\>)/';
        const ElseSyntax = '/(\@\?\-\>)/';
        const EndifSyntax = '/(\@\?\>)/';
        /**
         * @var string
         */
        private $registryFunction;
        /**
         * @var string
         */
        private $iocRegistry;

        public function __construct(BladeCompiler $blade, Application $application, $registryFunction = 'bladedCommand', $iocRegistry = 'bladed')
        {
            $this->blade = $blade;
            $this->application = $application;
            $this->registryFunction = $registryFunction;
            $this->iocRegistry = $iocRegistry;
            $this->register();
        }

        /**
         * @param string $namespace
         * @param $responsibleHandler
         */
        public function registerNamespace($namespace, $responsibleHandler)
        {
            $this->namespaces[$namespace] = $responsibleHandler;

            $this->application->singleton("{$this->iocRegistry}.{$namespace}", function () use ($responsibleHandler)
            {
                return new $responsibleHandler($this->application);
            });
        }

        /**
         * @param string[] $namespaces
         */
        public function registerNamespaces(array $namespaces = [])
        {
            foreach($namespaces as $namespace => $responsible)
            {
                $this->registerNamespace($namespace, $responsible);
            }
        }

        /**
         * @param $name
         * @throws Exception
         * @return \LaravelCommode\Bladed\Interfaces\IBladedCommand
         */
        public function getNamespace($name)
        {
            if (!array_key_exists($name, $this->namespaces))
            {
                throw new Exception("Unknown blade command namespace - {$name}");
            }

            return $this->application["{$this->iocRegistry}.{$name}"];
        }

        protected function getStateParser()
        {
            return function($matches)
            {

                $result = "<?php echo {$this->registryFunction}('{$matches[2]}', \$__env)->{$matches[4]}{$matches[5]} ?>";

                return $result;
            };
        }

        protected function getCacheStateParser()
        {
            return function($matches)
            {
                $result = eval($str = "return {$this->registryFunction}('{$matches[2]}', app('view'))->{$matches[4]}{$matches[5]};");
                return $result;
            };
        }

        protected function getQuestParser()
        {
            return function($matches)
            {
                $result = "<?php if({$this->registryFunction}('{$matches[2]}', \$__env)->{$matches[4]}{$matches[5]}): ?>";

                return $result;
            };
        }

        protected function getQuestFalseParser()
        {
            return function($matches)
            {
                $result = "<?php if(!{$this->registryFunction}('{$matches[2]}', \$__env)->{$matches[4]}{$matches[5]}): ?>";

                return $result;
            };
        }

        protected function getTplParser()
        {
            return function($matches) {
                $bag = $matches[2];
                $method = $matches[4];
                $template = $matches[7];
                $params = $matches[13];

                $result = "<?php echo {$this->registryFunction}('{$bag}', \$__env)->{$method}";


                $template = str_replace('$', '(:var)', $template);
                $template = str_replace('<?php', '(:<php)', $template);
                $template = str_replace('<?=', '(:<ephp)', $template);
                $template = str_replace('?>', '(:php>)', $template);

                $result .= '((new \LaravelCommode\Bladed\Compilers\TemplateCompiler(\Bladed::getStringCompiler(), $__env))->setTemplate("'.addslashes($template).'")'.($params == '' ? '': ', '.$params).') ?>';

                return $result;
            };
        }

        protected function getCacheTplParser()
        {
            return function($matches) {
                $bag = $matches[2];
                $method = $matches[4];
                $template = $matches[7];
                $params = $matches[13];

                $result = "return {$this->registryFunction}('{$bag}', app('view'))->{$method}";


                $template = str_replace('$', '(:var)', $template);
                $template = str_replace('<?php', '(:<php)', $template);
                $template = str_replace('<?=', '(:<ephp)', $template);
                $template = str_replace('?>', '(:php>)', $template);

                $result .= '((new \LaravelCommode\Bladed\Compilers\TemplateCompiler(\Bladed::getStringCompiler(), app("view")))->setTemplate("'.addslashes($template).'")'.($params == '' ? '': ', '.$params).') ?>';

                return eval($result);
            };
        }

        protected function getEachParser()
        {
            return function($matches)
            {
                $return = '<?php ';
                switch($matches[2])
                {
                    case 'down':
                        $return .= 'for('.$matches[5].'=count('.$matches[3].')-1;';
                        $return .= $matches[5].'>0;';
                        $return .= $matches[5].'--): ?>';

                        if ($matches[9] !== '') {
                            $return .= "\n".'<?php '.$matches[9].'= '.$matches[3].'['.$matches[5].'];?>';
                        }

                        break;
                    case 'up':
                        $return .= 'for('.$matches[5].'=0;';
                        $return .= $matches[5].'<count('.$matches[3].');';
                        $return .= $matches[5].'++): ?>';

                        if ($matches[9] !== '') {
                            $return .= "\n".'<?php '.$matches[9].' = '.$matches[3].'['.$matches[5].'];?>';
                        }

                        break;
                    case 'in':
                        $return .= 'foreach('.$matches[3].' as ';
                        $return .= ($matches[7] === '') ? $matches[5] : ($matches[9].' => '.$matches[5]);
                        $return .= '): ?>';
                        break;
                }

                return $return;
            };
        }

        protected function getEndCycleParser()
        {
            return function($matches) {
                switch($matches[2])
                {
                    case 'up':
                    case 'down':
                        return '<?php endfor; ?>';
                    case 'in':
                        return '<?php endforeach; ?>';
                }

                return '';
            };
        }

        protected function register()
        {
            $rules = [
                self::State         =>  $this->getStateParser(),
                self::Quest         =>  $this->getQuestParser(),
                self::QuestFalse    =>  $this->getQuestFalseParser(),
                self::Each          =>  $this->getEachParser(),
                self::ElseSyntax    =>  function() { return '<?php else: ?>'; },
                self::EndifSyntax   =>  function() { return '<?php endif; ?>'; },
                self::EndEach       =>  $this->getEndCycleParser(),
                self::Template      =>  $this->getTplParser(),
                self::CacheTemplate      =>  $this->getCacheTplParser(),
                self::CacheState         =>  $this->getCacheStateParser()
            ];

            $this->blade->extend(function($view) use ($rules)
            {
                foreach($rules as $regexp => $callback) {
                    $view = preg_replace_callback($regexp, $callback, $view);
                }

                return $view;
            });
        }

        /**
         * @return string
         */
        public function getRegistryFunction()
        {
            return $this->registryFunction;
        }

        /**
         * @param string $registryFunction
         * @return $this
         */
        public function setRegistryFunction($registryFunction)
        {
            $this->registryFunction = $registryFunction;
            return $this;
        }

        /**
         * @return string
         */
        public function getIoCRegistry()
        {
            return $this->iocRegistry;
        }

        /**
         * @param string $iocRegistry
         * @return $this
         */
        public function setIoCRegistry($iocRegistry)
        {
            $this->iocRegistry = $iocRegistry;
            return $this;
        }
    }