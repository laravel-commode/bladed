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
        const STATE = '/(@)([a-zA-Z]{1,})(\.)([a-zA-Z]{1,})(.*?)(\ {0,})(\@\>)/is';

        /**
         * call syntax
         * @::@namespace.method(?$args) @>
         * @::@namespace.property @>
         */
        const CACHE_STATE = '/(@\:\:)([a-zA-Z]{1,})(\.)([a-zA-Z]{1,})(.*?)(\ {0,})(\@\>)/is';

        /**
         * if syntax
         * @?namespace.method(?$args) ?@>
         * @?namespace.property ?@>
         */
        const QUEST = '/(\@\?)([a-zA-Z]{1,})(\.)([a-zA-Z]{1,})(.*?)(\ {0,})(\?\@\>)/is';

        /**
         * unless syntax
         * @!?namespace.method(?$args) ?@>
         * @!?namespace.property ?@>
         */
        const QUEST_FALSE = '/(\@\!\?)([a-zA-Z]{1,})(\.)([a-zA-Z]{1,})(.*?)(\ {0,})(\?\@\>)/is';

        /**
         * template syntax
         * @|namespace.method {
         *     multistring template blade text
         * }|(T_STRING, ?$args[])@>
         */
        const TEMPLATE = '/(\@\|)([a-z]{1,})(\.)([a-z]{1,})(\ {0,})(\{)(.*?)(\ {0,})(\})(\ {0,})(\|)(\()(.*?)(\))(\@)(\>)/is';

        /**
         * template syntax
         * @|namespace.method {
         *     multistring template blade text
         * }|(T_STRING, ?$args[])@>
         */
        const CACHE_TEMPLATE = '/(\@\:\:\|)([a-z]{1,})(\.)([a-z]{1,})(\ {0,})(\{)(.*?)(\ {0,})(\})(\ {0,})(\|)(\()(.*?)(\))(\@)(\>)/is';

        /**
         * foreach syntax
         * @in($collection||$value)
         * @in($collection||$key||$value)
         */
        const EACH = '/(\@(in|up|down)\()([\$\[\]\:\.\w\d\(\)\_\>\'\"\,\ \-]{1,})(\|{2})(\$[\w]{1,})(((\|{2})(\$[\w]{1,})){0,1})(\))/';
        const END_EACH = '/(\@(in|up|down)\>)/';
        const ELSE_SYNTAX = '/(\@\?\-\>)/';
        const END_IF_SYNTAX = '/(\@\?\>)/';
        /**
         * @var string
         */
        private $registryFunction;
        /**
         * @var string
         */
        private $iocRegistry;

        /** @noinspection MoreThanThreeArgumentsInspection
         * @param BladeCompiler $blade
         * @param Application $application
         * @param string $registryFunction
         * @param string $iocRegistry
         */
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

            return $this->application->make("{$this->iocRegistry}.{$name}");
        }

        protected function getScope()
        {
            $evalScope = function ($eval, $scope) {
                extract($scope);
                return eval($eval);
            };

            return $evalScope->bindTo(null);
        }

        protected function composeTemplate($template)
        {
            return strtr($template, [
                '$'     => '(:var)',
                '<?php' => '(:<php)',
                '<?='   => '(:<ephp)',
                '?>'    => '(:php>)'
            ]);
        }

        protected function getStateParser()
        {
            return function($matches)
            {
                return "<?php echo {$this->registryFunction}('{$matches[2]}', \$__env)->{$matches[4]}{$matches[5]} ?>";
            };
        }

        protected function getCacheStateParser()
        {
            $evalScope = $this->getScope();

            return function($matches) use ($evalScope)
            {
                $eval = "return {$this->registryFunction}('{$matches[2]}', \$app->make('view'))->{$matches[4]}{$matches[5]};";
                return $evalScope($eval, ['app' => $this->application]);
            };
        }

        protected function getQuestParser()
        {
            return function($matches)
            {
                return "<?php if({$this->registryFunction}('{$matches[2]}', \$__env)->{$matches[4]}{$matches[5]}): ?>";
            };
        }

        protected function getQuestFalseParser()
        {
            return function($matches)
            {
                return "<?php if(!{$this->registryFunction}('{$matches[2]}', \$__env)->{$matches[4]}{$matches[5]}): ?>";
            };
        }

        protected function getTplParser()
        {
            return function($matches) {
                $template = $this->composeTemplate($matches[7]);

                return $result = '<?php echo '.$this->registryFunction.'(\''.
                    $matches[2].'\', $__env)->'.$matches[4].'('.
                    '(new \LaravelCommode\Bladed\Compilers\TemplateCompiler(\Bladed::getStringCompiler(), $__env)'.
                    ')->setTemplate("'.addslashes($template).'")'.($matches[13] === '' ? '': ', '.$matches[13]).') ?>';
            };
        }

        protected function getCacheTplParser()
        {
            $evalScope = $this->getScope();

            return function($matches) use ($evalScope) {
                $bag = $matches[2];
                $method = $matches[4];
                $params = $matches[13];

                $result = "return {$this->registryFunction}('{$bag}', \$app->make('view'))->{$method}";


                $template = $this->composeTemplate($matches[7]);

                $result .= '((new \LaravelCommode\Bladed\Compilers\TemplateCompiler($app->make("commode.bladed")->getStringCompiler(), $app->make("view")))->setTemplate("'.addslashes($template).'")'.($params == '' ? '': ', '.$params).') ?>';

                return $evalScope($result, ['app' => $this->application]);
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
                        $return .= $matches[5].'<$____length=count('.$matches[3].');';
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
                return $matches[2] === 'up' || $matches[2] === 'down' ? '<?php endfor; ?>' : '<?php endforeach; ?>';
            };
        }

        protected function register()
        {
            $rules = [
                self::STATE         =>  $this->getStateParser(),
                self::QUEST         =>  $this->getQuestParser(),
                self::QUEST_FALSE    =>  $this->getQuestFalseParser(),
                self::EACH          =>  $this->getEachParser(),
                self::ELSE_SYNTAX    =>  function() { return '<?php else: ?>'; },
                self::END_IF_SYNTAX   =>  function() { return '<?php endif; ?>'; },
                self::END_EACH       =>  $this->getEndCycleParser(),
                self::TEMPLATE      =>  $this->getTplParser(),
                self::CACHE_TEMPLATE      =>  $this->getCacheTplParser(),
                self::CACHE_STATE         =>  $this->getCacheStateParser()
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