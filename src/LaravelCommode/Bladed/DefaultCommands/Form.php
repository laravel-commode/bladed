<?php
    namespace LaravelCommode\Bladed\DefaultCommands;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Foundation\Application;
    use Illuminate\Html\FormBuilder;
    use LaravelCommode\Bladed\Commands\ABladedCommand;
    use LaravelCommode\Bladed\Commands\ADelegateBladedCommand;
    use LaravelCommode\Bladed\DefaultCommands\Form\MetaManager;
    use LaravelCommode\Common\Meta\LocalizedMeta\MetaData;

    /**
     * Created by PhpStorm.
     * User: madman
     * Date: 03.02.15
     * Time: 21:28
     */
    class Form extends ADelegateBladedCommand
    {
        /**
         * @var FormBuilder
         */
        private $laraForm;

        private $models = [];

        /**
         * @var MetaManager
         */
        private $metaManager;

        public function __construct(Application $application)
        {
            parent::__construct($application);
            $this->laraForm = $application->make('form');
            $this->metaManager = new MetaManager();
            \phpQuery::newDocument();
        }

        protected function createElement($element)
        {
            return \phpQuery::pq("<{$element}></{$element}>");
        }

        protected function wrapPQ($value)
        {
            return \phpQuery::pq($value);
        }

        //<editor-fold desc="Inputs and Form">

        public function open(array $options = [])
        {
            $this->models[] = null;
            return $this->laraForm->open($options);
        }

        public function close()
        {
            $this->unsetModel();
            return $this->laraForm->close();
        }

        public function select($name, array $list = [], $selected = null, array $parameters = [])
        {
            return $this->wrapPQ($this->laraForm->select($name, $list, $selected, $parameters));
        }

        public function submit($value, array $options = [])
        {
            return $this->wrapPQ($this->laraForm->submit($value,$options));
        }

        public function label($text)
        {
            return $this->wrapPQ('<label>')->html($text);
        }

        public  function hidden($field, $value = null, array $options = [])
        {
            return $this->wrapPQ($this->laraForm->hidden($field, $value, $options));
        }

        public function text($field, $value = null, array $options = [])
        {
            $textBox = $this->wrapPQ($this->laraForm->text($field, $value, $options));

            if ($this->metaManager->currentMetaExists()) {
                $textBox->attr('placeholder', $this->metaManager->getCurrentMetaValue($field));
            }

            return $textBox;
        }

        public function password($field, array $options = [])
        {
            $password = $this->wrapPQ($this->laraForm->password($field, $options));;

            if ($this->metaManager->currentMetaExists()) {
                $password->attr('placeholder', $this->metaManager->getCurrentMetaValue($field));
            }

            return $password;
        }

        public function textarea($field, $value = null, array $options = [])
        {
            $textarea = $this->wrapPQ($this->laraForm->textarea($field, $value, $options));

            if ($this->metaManager->currentMetaExists()) {
                $textarea->attr('placeholder', $this->metaManager->getCurrentMetaValue($field));
            }
            return $textarea;
        }

        public function checkbox($field, $value = null, $checked = null, array $options = [])
        {
            return $this->wrapPQ($this->laraForm->checkbox($field, $value, $checked, $options));
        }
        public function radio($field, $value = null, $checked = null, array $options = [])
        {
            return $this->wrapPQ($this->laraForm->radio($field, $value, $checked, $options));
        }


        //</editor-fold>

        //<editor-fold desc="Working with models">


        public function unsetModel()
        {
            array_pop($this->models);
        }

        public function model($model, array $options = [])
        {
            $this->models[] = $model;
            return $this->getDelegate()->model(last($this->models), $options);
        }

        /**
         * @return mixed|Model
         */
        public function currentModel()
        {
            return last($this->models);
        }

        /**
         * @return Model[]|\mixed[]
         */
        public function getModels()
        {
            return $this->models;
        }

        /**
         * @param $model
         */
        public function setModel($model)
        {
            $this->models[] = $model;
        }

        //</editor-fold>

        //<editor-fold desc="Working with MetaData">
        /**
         * @param $field
         * @param string $after
         * @param string $before
         * @return \phpQuery|\QueryTemplatesParse|\QueryTemplatesSource|\QueryTemplatesSourceQuery|string
         */
        public function meta($field, $after = null, $before = null)
        {
            $element = 'label';

            if ($this->metaManager->currentMetaExists()) {
                $element = $this->metaManager->getCurrentElement();
                $field = $this->metaManager->getCurrentMetaValue($field);
            }

            return $this->createElement($element)->html(trim(implode(' ', [$before, $field, $after])));
        }

        public function setMeta(MetaData $meta, $metaType = 'label')
        {
            $this->metaManager->addMeta($meta, $metaType);
        }

        public function unsetMeta()
        {
            $this->metaManager->unsetCurrentMeta();
        }

        public function getMeta()
        {
            return $this->metaManager->getCurrentMeta();
        }

        public function assignMeta(&$meta = null)
        {
            $meta = $this->metaManager->currentMetaExists() ? $this->metaManager->getCurrentMeta() : null;
        }
        //</editor-fold>

        public function getDelegate()
        {
            return $this->laraForm;
        }
    }