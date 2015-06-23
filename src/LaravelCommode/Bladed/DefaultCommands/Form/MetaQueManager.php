<?php

namespace LaravelCommode\Bladed\DefaultCommands\Form;

use LaravelCommode\Utils\Meta\Localization\MetaAttributes;

class MetaQueManager
{
    private $metas = [];

    public function addMeta(MetaAttributes $meta, $type = 'label')
    {
        $this->metas[] = ['meta' => $meta, 'element' => $type];
        return $this;
    }

    public function unsetCurrentMeta()
    {
        array_pop($this->metas);
        return $this;
    }

    public function getCurrentMeta()
    {
        return last($this->metas)['meta'];
    }

    public function getCurrentMetaValue($key)
    {
        if (!$this->currentMetaExists()) {
            return "<!-- {$key} -->";
        }

        return $this->getCurrentMeta()->{$key};
    }

    public function getCurrentElement()
    {
        return last($this->metas)['element'];
    }

    public function currentMetaExists()
    {
        return @last($this->metas) !== false;
    }
}
