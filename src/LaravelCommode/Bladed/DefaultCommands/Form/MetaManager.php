<?php
    namespace LaravelCommode\Bladed\DefaultCommands\Form;
    use LaravelCommode\Common\Meta\LocalizedMeta\MetaData;

    /**
     * Created by PhpStorm.
     * User: madman
     * Date: 04.02.15
     * Time: 19:21
     */
    class MetaManager
    {
        private $metas = [];

        public function addMeta(MetaData $meta, $type = 'label')
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
            return @last($this->metas) != null;
        }
    }