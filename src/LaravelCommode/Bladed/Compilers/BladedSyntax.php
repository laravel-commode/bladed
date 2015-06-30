<?php

namespace LaravelCommode\Bladed\Compilers;

class BladedSyntax
{

    const STATEMENT = '/(\@)([\w\_\d]{1,})(\.)([\w\_\d]{1,})(?P<parameters>\(.*?\)){0,}(\ \@\>)/is';
    const STATEMENT_CACHED = '/(\@\:)([\w\_\d]{1,})(\.)([\w\_\d]{1,})(?P<parameters>\(.*?\)){0,}(\ \@\>)/is';

    const CONDITION = '/(\@\?)([\w\_\d]{1,})(\.)([\w\_\d]{1,})(?P<parameters>\(.*?\)){0,}(\ \@\>)/is';
    const CONDITION_ELSE = '/(\@\?\-\>)/is';
    const CONDITION_END = '/(\@\?\>)/is';
    const CONDITION_UNLESS = '/(\@\?\!)([\w\_\d]{1,})(\.)([\w\_\d]{1,})(?P<parameters>\(.*?\)){0,}(\ \@\>)/is';

    const TEMPLATE = '/(\@\|)([\w\_\d]{1,})(\.)([\w\_\d]{1,})(?P<parameters>\(.*?\))\s*(?P<body>\{.*?\})(\ \@\>)/is';
    const TEMPLATE_CACHED = '/(\@\|\:)([\w\_\d]{1,})(\.)([\w\_\d]{1,})(?P<parameters>\(.*?\))\s*(?P<body>\{.*?\})(\ \@\>)/is';

    const ITERATORS =
        '/(@)(each|up|down)(\()(.*?)(\, {0,})(\&{0,1}\$[\w\s]{1,})(((\, {0,})(\$[\w\s]{1,})){0,1})(\))/is';

    const ITERATORS_END = '/(@)(each|up|down)(\ {0,}\@\>)/is';

}

