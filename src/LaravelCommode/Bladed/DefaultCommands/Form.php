<?php
namespace LaravelCommode\Bladed\DefaultCommands;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Html\FormBuilder;

use LaravelCommode\Bladed\Commands\ADelegateBladedCommand;
use LaravelCommode\Bladed\DefaultCommands\Form\MetaQueManager;
use LaravelCommode\Bladed\DefaultCommands\Form\MetaStack;
use LaravelCommode\Bladed\DefaultCommands\Form\ModelStack;
use LaravelCommode\Utils\Meta\Localization\MetaAttributes;

class Form extends ADelegateBladedCommand
{
    /**
     * @var FormBuilder
     */
    private $laraForm;

    private $models = [];

    /**
     * @var MetaStack|MetaAttributes[]
     */
    private $metaStack;

    /**
     * @var ModelStack
     */
    private $modelStack;

    public function __construct(Application $application)
    {
        parent::__construct($application);
        $this->modelStack = new ModelStack();
        $this->metaStack = new MetaStack();
    }

    protected function createElement($element)
    {
        return pq("<{$element}></{$element}>");
    }

    protected function wrapPQ($value)
    {
        return pq($value);
    }

    //<editor-fold desc="Inputs and Form">

    public function open(array $options = [])
    {
        $this->models[] = null;
        return $this->getDelegate()->open($options);
    }

    public function close()
    {
        $this->unsetModel();
        return $this->getDelegate()->close();
    }

    public function select($name, array $list = [], $selected = null, array $parameters = [])
    {
        return $this->wrapPQ($this->getDelegate()->select($name, $list, $selected, $parameters));
    }

    public function submit($name, array $options = [])
    {
        return $this->wrapPQ($this->getDelegate()->submit($name, $options));
    }

    public function label($text)
    {
        return $this->createElement('label')->html($text);
    }

    public function hidden($field, $value = null, array $options = [])
    {
        return $this->wrapPQ($this->getDelegate()->hidden($field, $value, $options));
    }

    public function text($field, $value = null, array $options = [])
    {
        $textBox = $this->wrapPQ($this->getDelegate()->text($field, $value, $options));

        if (($meta = $this->getMeta()) !== null) {
            $textBox->attr('placeholder', $meta->__get($field));
        }

        return $textBox;
    }

    public function password($field, array $options = [])
    {
        $password = $this->wrapPQ($this->getDelegate()->password($field, $options));

        if (($meta = $this->getMeta()) !== null) {
            $password->attr('placeholder', $meta->__get($field));
        }

        return $password;
    }

    public function textarea($field, $value = null, array $options = [])
    {
        $textarea = $this->wrapPQ($this->getDelegate()->textarea($field, $value, $options));

        if (($meta = $this->getMeta()) !== null) {
            $textarea->attr('placeholder', $meta->__get($field));
        }

        return $textarea;
    }

    public function checkbox($field, $value = null, $checked = null, array $options = [])
    {
        return $this->wrapPQ($this->getDelegate()->checkbox($field, $value, $checked, $options));
    }

    public function radio($field, $value = null, $checked = null, array $options = [])
    {
        return $this->wrapPQ($this->getDelegate()->radio($field, $value, $checked, $options));
    }


    //</editor-fold>

    //<editor-fold desc="Working with models">


    public function unsetModel()
    {
        if (!$this->modelStack->isEmpty()) {
            $this->modelStack->pop();
        }
    }

    public function model($model, array $options = [])
    {
        $this->setModel($model);
        return $this->getDelegate()->model($this->getModel(), $options);
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->modelStack->isEmpty() ? null : $this->modelStack->top();
    }

    /**
     * @param $model
     */
    public function setModel($model)
    {
        $this->modelStack->push($model);
    }

    //</editor-fold>

    //<editor-fold desc="Working with MetaAttributes">
    /**
     * @param $field
     * @param string $after
     * @param string $before
     * @return \phpQuery|string
     */
    public function meta($field, $after = null, $before = null)
    {
        if ($this->metaStack->isEmpty()) {
            throw new \LogicException("No meta available to extract '{$field}'' value.");
        }

        $element = pq($this->getMeta()->element($field));

        if ($after !== null) {
            $element->append($after);
        }

        if ($before !== null) {
            $element->prepend($before);
        }

        return $element;
    }

    public function setMeta(MetaAttributes $meta)
    {
        $this->metaStack->push($meta);
    }

    public function unsetMeta()
    {
        if (!$this->metaStack->isEmpty()) {
            $this->metaStack->pop();
        }
    }

    /**
     * @return MetaAttributes
     */
    public function getMeta()
    {
        return $this->metaStack->isEmpty() ? null : $this->metaStack->top();
    }

    /**
     * @param null $meta
     */
    public function assignMeta(&$meta = null)
    {
        $meta = $this->getMeta();
    }
    //</editor-fold>

    public function getDelegate()
    {
        if ($this->laraForm === null) {
            return $this->laraForm = $this->getApplication()->make('form');
        }

        return $this->laraForm;
    }
}
