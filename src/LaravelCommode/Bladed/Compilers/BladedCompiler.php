<?php

namespace LaravelCommode\Bladed\Compilers;

use Exception;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\View\Compilers\BladeCompiler;

use LaravelCommode\Bladed\BladedServiceProvider;
use LaravelCommode\Bladed\Interfaces\IBladedCommand;
use UnexpectedValueException;

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
     * @var Application
     */
    private $application;

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
    public function __construct(
        BladeCompiler $blade,
        Application $application,
        $registryFunction = 'bladed',
        $iocRegistry = BladedServiceProvider::PROVIDES_SERVICE
    ) {
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

        $this->application->singleton("{$this->iocRegistry}.{$namespace}", function () use ($responsibleHandler) {
            return new $responsibleHandler($this->application);
        });
    }

    /**
     * @param string[] $namespaces
     */
    public function registerNamespaces(array $namespaces = [])
    {
        foreach ($namespaces as $namespace => $responsible) {
            $this->registerNamespace($namespace, $responsible);
        }
    }

    /**
     * @param $name
     * @throws UnexpectedValueException
     * @return \LaravelCommode\Bladed\Interfaces\IBladedCommand
     */
    public function getNamespace($name)
    {
        if (!array_key_exists($name, $this->namespaces)) {
            throw new UnexpectedValueException("Unknown blade command namespace - {$name}.");
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
        return TemplateCompiler::composeTemplate($template);
    }

    protected function getStateParser()
    {
        return function ($matches) {
            return "<?php echo {$this->registryFunction}('{$matches[2]}', \$__env)->{$matches[4]}{$matches[5]} ?>";
        };
    }

    protected function getCacheStateParser()
    {
        $evalScope = $this->getScope();

        return function ($matches) use ($evalScope) {

            $eval = "return {$this->registryFunction}('{$matches[2]}',".
                    " \$app->make('view'))->{$matches[4]}{$matches[5]};";

            return $evalScope($eval, ['app' => $this->application]);
        };
    }

    protected function getConditionParser()
    {
        return function ($matches) {
            return  "<?php if ({$this->registryFunction}('{$matches[2]}', \$__env)".
                    "->{$matches[4]}{$matches[5]}): ?>";
        };
    }

    protected function getUnlessConditionParser()
    {
        return function ($matches) {
            return "<?php if (!{$this->registryFunction}('{$matches[2]}', \$__env)->{$matches[4]}{$matches[5]}): ?>";
        };
    }

    protected function getTemplateParser()
    {
        return function ($matches) {
            $matches['body'] = $this->composeTemplate(
                substr($matches['body'], 1, mb_strlen($matches['body']) - 2)
            );

            $matches['parameters'] = substr($matches['parameters'], 1, mb_strlen($matches['parameters']) - 2);

            $result =   '<?php echo '.$this->registryFunction.'(\''.$matches[2].'\', $__env)->';
            $result.=   $matches[4].'(';    //  start call
            $result.=   'bladedTemplate($__env)';
            $result.=   '->setTemplate("'.$matches['body'].'")';
            $result.=   ($matches['parameters'] === '' ? '' : ', '.$matches['parameters']).') ?>';

            return $result;
        };
    }

    protected function getCacheTplParser()
    {
        $evalScope = $this->getScope();

        return function ($matches) use ($evalScope) {
            $matches['body'] = addslashes(
                $this->composeTemplate(substr($matches['body'], 1, mb_strlen($matches['body']) - 2))
            );

            $matches['parameters'] = substr($matches['parameters'], 1, mb_strlen($matches['parameters']) - 2);

            $result =   'return '.$this->registryFunction.'(\''.$matches[2].'\', app()->make(\'view\'))->';
            $result.=   $matches[4].'(';    //  start call
            $result.=   'bladedTemplate(app()->make(\'view\'))';
            $result.=   '->setTemplate("'.$matches['body'].'")';
            $result.=   ($matches['parameters'] === '' ? '' : ', '.$matches['parameters']).');';

            return $evalScope($result, ['app' => $this->application]);
        };
    }

    protected function getIteratorParser()
    {
        return function ($matches) {
            $return = '<?php ';
            switch($matches[2])
            {
                case 'each':
                    $return .= 'foreach('.$matches[4].' as ';
                    $return .= ($matches[10] === '') ? $matches[6] : ($matches[10].' => '.$matches[6]);
                    return $return . '): ?>';
                    break;
                case 'down':
                    $countVar = "$".uniqid('count');

                    $return .= $countVar . " = count({$matches[4]}) - 1; ";

                    $iterationKey = $matches[10] ?: uniqid('$key');

                    $return .= "for({$iterationKey} = {$countVar}; {$iterationKey} > 0; {$iterationKey}--): ?>\n";
                    $return .= "<?php {$matches[6]} = {$matches[4]}[{$iterationKey}] ?>";

                    break;
                case 'up':
                    $countVar = "$".uniqid('count');

                    $return .= $countVar . " = count({$matches[4]}); ";

                    $iterationKey = $matches[10] ?: uniqid('$key');

                    $return .= "for({$iterationKey} = 0; {$iterationKey} < {$countVar}; {$iterationKey}++): ?>\n";
                    $return .= "<?php {$matches[6]} = {$matches[4]}[{$iterationKey}] ?>";

                    break;
            }

            return $return;
        };
    }

    protected function getIteratorEndParser()
    {
        return function ($matches) {
            return $matches[2] === 'up' || $matches[2] === 'down' ? '<?php endfor; ?>' : '<?php endforeach; ?>';
        };
    }

    protected function register()
    {
        $rules = [
            BladedSyntax::STATEMENT         =>  $this->getStateParser(),

            BladedSyntax::CONDITION         =>  $this->getConditionParser(),
            BladedSyntax::CONDITION_UNLESS  =>  $this->getUnlessConditionParser(),
            BladedSyntax::CONDITION_ELSE    =>  function () {
                return '<?php else: ?>';
            },
            BladedSyntax::CONDITION_END    =>  function () {
                return '<?php endif; ?>';
            },

            BladedSyntax::ITERATORS         =>  $this->getIteratorParser(),
            BladedSyntax::ITERATORS_END     =>  $this->getIteratorEndParser(),

            BladedSyntax::STATEMENT_CACHED  =>  $this->getCacheStateParser(),

            BladedSyntax::TEMPLATE          =>  $this->getTemplateParser(),

            BladedSyntax::TEMPLATE_CACHED   =>  $this->getCacheTplParser(),

        ];

        $this->blade->extend(function ($view) use ($rules) {
            foreach ($rules as $regexp => $callback) {
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