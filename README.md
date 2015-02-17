#Commode: Bladed

[![Build Status](https://travis-ci.org/laravel-commode/bladed.svg?branch=master)](https://travis-ci.org/laravel-commode/bladed)
[![Code Climate](https://codeclimate.com/github/laravel-commode/bladed/badges/gpa.svg)](https://codeclimate.com/github/laravel-commode/bladed)
[![Coverage Status](https://coveralls.io/repos/laravel-commode/bladed/badge.svg?branch=master)](https://coveralls.io/r/laravel-commode/bladed?branch=master)

>**_laravel-commode/bladed_** is an extension package for laravel's Blade templating system.

<br />
####Contents

+ <a href="#installing">Installing</a>
+ <a href="https://github.com/laravel-commode/bladed/wiki/01.-Syntax">Syntax</a>
    + <a href="https://github.com/laravel-commode/bladed/wiki/01.-Syntax#statements">Statements</a>
    + <a href="https://github.com/laravel-commode/bladed/wiki/01.-Syntax#cached_statement">Cached statements</a>
    + <a href="https://github.com/laravel-commode/bladed/wiki/01.-Syntax#condition">Conditions</a>
    + <a href="https://github.com/laravel-commode/bladed/wiki/01.-Syntax#loops">Loops</a>
    + <a href="https://github.com/laravel-commode/bladed/wiki/01.-Syntax#wrappers">Wrappers</a>
    + <a href="https://github.com/laravel-commode/bladed/wiki/01.-Syntax#cached_wrappers">Cached wrappers</a>
+ <a href="https://github.com/laravel-commode/bladed/wiki/02.-Command-namespace">Creating a command namespace</a>
+ <a href="https://github.com/laravel-commode/bladed/wiki/03.-Wrappers">Wrappers</a>
+ <a href="#commands">Available commands by default</a>
+ <a href="#reference">IoC reference table</a>

##<a name="installing">Installing</a>

You can install laravel-commode/validation-locator using composer:
    
    "require": {
        "laravel-commode/bladed": "dev-master"
    }
    
To enable package you need to register LaravelCommode\Bladed\BladedServiceProvider 
service provider in your application config.

    <?php
        // ./yourLaravelApplication/app/config/app.php
        return [
            // ... config code
            'providers' => [
                // ... providers
                'LaravelCommode\Bladed\BladedServiceProvider'
            ]
        ];


<!--      

##<a name="creating_command">Creating a command namespace</a>

`Bladed` package was developed to provide more possibilities for view logic, and basically to let the final 
user to create extensions or widgets based upon this mechanism. So to start working with it you need to do couple 
of things: create a **command provider** and register it in **bladed manager**.
 
**Command provider** is a class that extends `LaravelCommode\Bladed\Commands\ABladedCommand` or 
`LaravelCommode\Bladed\Commands\ADelegateCommand` and provides a set of **template commands**(methods) that 
might contain template rendering logic or provide an interface to communicate with application services. 
**Command provider** has straight access to application's IoC container and to view environment from two 
protected methods: `ABladedCommand::getApplication()` and `ABladedCommand::getEnvironment()`. All 
**command providers** are constructed in IoC container, so feel free to override constructors, but don't 
forget to trigger the parent's one. **Command provider** can be extended in a runtime, just like all 
macro-classes in laravel.

All **command providers** registered in **bladed manager** are basically available from laravel's IoC container 
as singletons.

##<a name="syntax">Syntax</a>

####<a name="statement">Statement</a>
    
Triggers command provider's method or property - all returned values will be rendered.
    
    @command.property @>                    - invokes property
    @command.method() @>                    - invokes method
    @command.method($param1, $paramN) @>    - invokes method with parameters
    @command.method($param1, $paramN)       - return chaining is available
        ->otherMethod($param1, $paramN) @> 
    

####<a name="cached_statement">Cached statement</a>

Triggers command provider's method or property - all returned values will be rendered only once.

    @::command.property @>                  - invokes property
    @::command.method() @>                  - invokes method
    @::command.method($param1, $paramN) @>  - invokes method with parameters
    @::command.method($param1, $paramN)     - return chaining is available
        ->otherMethod($param1, $paramN) @> 
    
####<a name="conditions">Conditions</a>

Conditions are base upon command provider's method execution results or properties.
    
    @?command.property ?@>      -   if command.property is true 
        Property is true                
    @?->                        -   else (optional)
        Property is false
    @?>                         -   end if
    
    @!?command.property ?@>     -   if command.property is not true 
        Property is false                
    @?->                        -   else (optional)
        Property is true
    @?>                         -   end if
    
    
    @?command.method() ?@>      -   if commandProvider.method returns true value
        Method returns true                
    @?->                        -   else (optional) 
        Method returns false
    @?>                         -   end if
    
    @?command.method() ?@>      -   if commandProvider.method doesn't return true value 
        Method returns false                
    @?->                        -   else (optional)
        Method returns true
    @?>                         -   end if


####<a name="loops">Loops</a>

Simple alternative for native @foreach and @for loops

    @in($collection||$value)            @foreach($collection as $value)
        {{$value}}                          {{$value}}
    @in>                                @endforeach
    
    @in($collection||$key||$value)      @foreach($collection as $key => $value)
        {{$key}} equals {{$value}}          {[$key}} equals {{$value}}
    @in>                                @endforeach
    
    @up($collection||$value)            @for($key = 0; $key < count($collection); $key++)
        {{$value}}                          <?php $value = $collection[$key]; ?>
    @up>                                    {{$value}}
                                        @endfor
                                        
    @up($collection||$key||$value)      @for($key = 0; $key < count($collection); $key++)
        {{$key}} equals {{$value}}          <?php $value = $collection[$key]; ?>
    @up>                                    {{$key}} equals {{$value}} 
                                        @endfor
    
    @down($collection||$value)          @for($key = count($collection) - 1; $key == 0; $key--)
        {{$value}}                          <?php $value = $collection[$key]; ?>
    @down>                                  {{$value}} 
                                        @endfor
    
    @down($collection||$key||$value)    @for($key = count($collection) - 1; $key == 0; $key--)
        {{$key}} equals {{$value}}        <?php $value = $collection[$key]; ?>
    @down>                                {{$key}} equals {{$value}} 
                                        @endfor
    

####<a name="template">Template</a>

`Bladed` extension provides **template action** system.

    @|command.templateMethod {
        This template can be rendered later. {{$renderTime}}
    }|()@>
    
    @|command.templateMethod {
        This template can be rendered later. {{$renderTime}}
    }|($param1, $paramN)@>
    
All method's that will dial templates will always always receive 
`LaravelCommode\Bladed\Compilers\TemplateCompiler` as first argument and then all the others.
    
####<a name="cached_template">Cached template</a>

**Template action** that will be rendered only at once.

    @::|command.method {
        This template can be rendered later. {{$renderTime}}
    }|()@>

    @::|command.method {
        This template can be rendered later. {{$renderTime}}
    }|($param1, $paramN)@>
    >
    
##<a name="templating">Templates</a>

`Bladed` provides a small template system. All methods that are supposed to be **template actions** will 
receive `LaravelCommode\Bladed\Compilers\TemplateCompiler` as first argument. You can pass or append variable 
values into it's parameter bag through two methods `TemplateCompiler::setArguments(array $params)` and 
`TemplateCompiler::appendArguments(array $params)`. Template can be rendered by calling 
`TemplateCompiler::render(array $params = [])`  or by forcing it's conversion into string.

Example bellow might be a bit useless in production, but still it shows the functionality and it might remind you 
ASP.NET Razor engine:

Command provider:

    <?php
        namespace Application\Bladed\Widgets\Form;
        
        use LaravelCommode\Bladed\Commands\ABladedCommand;
        use LaravelCommode\Bladed\Compilers\TemplateCompiler;
        
        class FormWidget extends ADelegateBladedCommand
        {
            public function getDelegate()
            {
                return $this->getApplication()->make('form');
            }
            
            public function wrapFormModel(TemplateCompiler $template, $viewModel, array $attributes = [])
            {
                return $this->open($viewModel, $attributes).
                    $template->setArguments($attributes).
                    $this->close();
            }
        }

Registering command with BladedManager facade in service:
    
        /** ...service provider code ... **/
        
        /**
        * Service provider registration
        */
        public function register()
        {
            \BladedManager::registerCommandNamespace('form', 'Application\Bladed\Widgets\Form\FormWidget');
            // or \BladedManager::registerCommandNamespaces(['form' => 'Application\Bladed\Widgets\Form\FormWidget']);
            
            // or $this->application->make('commode.bladed')-> ...
        }
        
View usage:

    @|form.wrapFormModel {
        
        @form.text('name', null, ['class' => 'form-control']) @>
        @form.password('password', ['class' => 'form-control']) @>
        
    }|($viewModel, $arguments)@>

##<a name="commands">Available commands by default</a>

###Scope 

Class responsible: `LaravelCommode\Bladed\DefaultCommands\Scope` <br />
Command namespace: `scope` <br />

Command description list:

<table width="100%">
    <tr>
        <th>Method</th>
        <th>Description</th>
        <th>Returns</th>
    </tr>
    <tr>
        <td>set(&$var, $value)</td>
        <td>
            Set's variables value for view scope, as 
            <code>&lt;?php $var = $value; ?&gt;</code>
        </td>
        <td>null</td>
    </tr>
    <tr>
        <td>setIf(&$var, $value)</td>
        <td>
            Set's variables value for view scope, as 
            <code>&lt;?php $var = isset($var) ? $var : $value; ?&gt;</code>
        </td>
        <td>null</td>
    </tr>
    <tr>
        <td>share($key, $value)</td>
        <td>
            Shares $value as $key within environment template, as 
            <code>&lt;?php $__env->share($key, $value) ?&gt;</code>
        </td>
        <td>null</td>
    </tr>
    <tr>
        <td>l($id, array $parameters = array(), $domain = 'messages', $locale = null)</td>
        <td>
            Triggers laravel's <code>trans($id, array $parameters = array(), $domain = 'messages', $locale = null)</code>
            function.
        </td>
        <td>string</td>
    </tr>
    <tr>
        <td>dd($parameterN)</td>
        <td>
            Triggers laravel's <code>dd($parameterN)</code>function.
        </td>
        <td>null</td>
    </tr>
    <tr>
        <td>var_dump($parameterN)</td>
        <td>
            Triggers <code>var_dump($parameterN)</code>function.
        </td>
        <td>null</td>
    </tr>
</table>
<
###Form

Form command provider wraps laravel's form builder and provides functionality for managing 
models, meta-data(via `LaravelCommode\Common\Meta\LocalizedMeta\MetaData`) and wraps all "inputs" with 
\phpQueryObject to provide more flexibility for form templates.

Class responsible: `LaravelCommode\Bladed\DefaultCommands\Form` <br />
Command namespace: `form` <br />

Command description list:

<table width="100%">
    <tr>
        <th>Method</th>
        <th>Description</th>
        <th>Returns</th>
    </tr>
    <tr>
        <td>open(array $attributes = [])</td>
        <td>
            Alternative to <code>Form::open(array $attributes = [])</code>
        </td>
        <td>string</td>
    </tr>
    <tr>
        <td>close()</td>
        <td>
            Alternative to <code>Form::close()</code>
        </td>
        <td>string</td>
    </tr>
    <tr>
        <td>model($model, array $attributes = [])</td>
        <td>
            Alternative to <code>Form::model($model, array $attributes = [])</code>
        </td>
        <td>string</td>
    </tr>
    <tr>
        <td>select($name, array $list = [], $selected = null, array $parameters = [])</td>
        <td>
            Alternative to <code>Form::select($name, array $list = [], $selected = null, array $parameters = [])</code>
        </td>
        <td>string|phpQueryObject</td>
    </tr>
    <tr>
        <td>submit($name, array $options = [])</td>
        <td>
            Alternative to <code>Form::submit($name, array $options = [])</code>
        </td>
        <td>string|phpQueryObject</td>
    </tr>
    <tr>
        <td>label($html)</td>
        <td>
            Returns label element with provided html
        </td>
        <td>string|phpQueryObject</td>
    </tr>
    <tr>
        <td>hidden($field, $value = null, array $options = [])</td>
        <td>
            Alternative to <code>Form::hidden($field, $value = null, array $options = [])</code>
        </td>
        <td>string|phpQueryObject</td>
    </tr>
    <tr>
        <td>text($field, $value = null, array $options = [])</td>
        <td>
            Alternative to <code>Form::text($field, $value = null, array $options = [])</code>.<br />
            If meta is available appends placeholder attribute.
        </td>
        <td>string|phpQueryObject</td>
    </tr>
    <tr>
        <td>textarea($field, $value = null, array $options = [])</td>
        <td>
            Alternative to <code>Form::textarea($field, $value = null, array $options = [])</code>.<br />
            If meta is available appends placeholder attribute.
        </td>
        <td>string|phpQueryObject</td>
    </tr>
    <tr>
        <td>password($field,  array $options = [])</td>
        <td>
            Alternative to <code>Form::password($field, array $options = [])</code>.<br />
            If meta is available appends placeholder attribute.
        </td>
        <td>string|phpQueryObject</td>
    </tr>
    <tr>
        <td>submit($name, array $options = [])</td>
        <td>
            Alternative to <code>Form::submit($name, array $options = [])</code>
        </td>
        <td>string|phpQueryObject</td>
    </tr>
    <tr>
        <td>checkbox($field, $value = null, $checked = null, array $options = [])</td>
        <td>
            Alternative to <code>Form::checkbox($field, $value = null, $checked = null, array $options = [])</code>
        </td>
        <td>string|phpQueryObject</td>
    </tr>
    <tr>
        <td>radio($field, $value = null, $checked = null, array $options = [])</td>
        <td>
            Alternative to <code>Form::radio($field, $value = null, $checked = null, array $options = [])</code>
        </td>
        <td>string|phpQueryObject</td>
    </tr>
</table>

##<a name="reference">IoC reference table</a>

-->