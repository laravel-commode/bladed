<?php

namespace LaravelCommode\Bladed\DefaultCommands\Form;

use LaravelCommode\Utils\DataStructures\TypeStack;
use LaravelCommode\Utils\Meta\Localization\MetaAttributes;

class MetaStack extends TypeStack
{
    /**
     * @return string
     */
    public function getType()
    {
        return MetaAttributes::class;
    }
}
