#Commode: Bladed

[![Build Status](https://travis-ci.org/laravel-commode/bladed.svg?branch=master)](https://travis-ci.org/laravel-commode/bladed)
[![Code Climate](https://codeclimate.com/github/laravel-commode/bladed/badges/gpa.svg)](https://codeclimate.com/github/laravel-commode/bladed)
[![Coverage Status](https://coveralls.io/repos/laravel-commode/bladed/badge.svg?branch=master)](https://coveralls.io/r/laravel-commode/bladed?branch=master)

>**_laravel-commode/bladed_** is an extension package for laravel's Blade templating system.

<br />
####Contents

+ <a href="#installing">Installing</a>
+ <a href="#creating_command">Creating a command namespace</a>
+ <a href="#syntax">Syntax</a>
    + <a href="#statement">Statement</a>
    + <a href="#cached_statement">Cached statement</a>
    + <a href="#condition">Conditions</a>
    + <a href="#loops">Loops</a>
    + <a href="#template">Template</a>
    + <a href="#cached_template">Cached template</a>
+ <a href="#installing">Templates</a>
+ <a href="#installing">Available commands by default</a>
+ <a href="#installing">IoC reference table</a>

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
        
##<a name="creating_command">Creating a command namespace</a>

`Bladed` package was developed to provide more possibilities for view logic, and basically to let the final 
user to create extensions or widgets based upon this mechanism. So to start working with it you need to do couple 
of things: create a **command provider** and register it in **bladed manager**.
 
**Command provider** is a class that extends `\LaravelCommode\Bladed\Commands\ABladedCommand` or 
`\LaravelCommode\Bladed\Commands\ADelegateCommand` and provides a set of **commands**(methods) that might 
contain template rendering logic or provide an interface to communicate with application services. **Command 
provider** have straight access to application's IoC container and to view environment from two protected 
methods: `ABladedCommand::getApplication()` and `ABladedCommand::getEnvironment()`. All **Command 
provider** are constructed in IoC container, so feel free to override constructors, but don't forget to call 
parent's one. **Command provider** can be extended in a runtime, just like all macro-classes in laravel.

All **command providers** registered in **bladed manager** are basically available from laravel's IoC container 
as singletons.

##<a name="syntax">Syntax</a>

###<a name="statement">Statement</a>
    
Triggers command provider's method or property - all returned values will be rendered.
    
    @command.property @>                    - invokes property
    @command.method() @>                    - invokes method
    @command.method($param1, $paramN) @>    - invokes method with parameters
    @command.method($param1, $paramN)       - return chaining is available
        ->otherMethod($param1, $paramN) @> 
    

###<a name="cached_statement">Cached statement</a>

Triggers command provider's method or property - all returned values will be rendered only once.

    @::command.property @>                  - invokes property
    @::command.method() @>                  - invokes method
    @::command.method($param1, $paramN) @>  - invokes method with parameters
    @::command.method($param1, $paramN)     - return chaining is available
        ->otherMethod($param1, $paramN) @> 
    
###<a name="conditions">Conditions</a>

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


###<a name="loops">Loops</a>

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
    
    