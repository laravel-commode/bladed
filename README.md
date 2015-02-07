#Commode: Bladed

[![Build Status](https://travis-ci.org/laravel-commode/bladed.svg?branch=master)](https://travis-ci.org/laravel-commode/bladed)
[![Code Climate](https://codeclimate.com/github/laravel-commode/bladed/badges/gpa.svg)](https://codeclimate.com/github/laravel-commode/bladed)
[![Coverage Status](https://coveralls.io/repos/laravel-commode/bladed/badge.svg?branch=master)](https://coveralls.io/r/laravel-commode/bladed?branch=master)

>**_laravel-commode/bladed_** is an extension package for laravel's Blade templating system.

<br />
####Contents

+ <a href="#installing">Installing</a>


##<a name="service">Installing</a>

You can install laravel-commode/validation-locator using composer:
    
    "require": {
        "laravel-commode/bladed": "dev-master"
    }
To enable package you need to register LaravelCommode\Bladed\BladedServiceProvider service provider in your application config.

    <?php
        // ./yourLaravelApplication/app/config/app.php
        return [
            // ... config code
            'providers' => [
                // ... providers
                'LaravelCommode\Bladed\BladedServiceProvider'
            ]
        ];