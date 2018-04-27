<?php

namespace Sledgehammer\Mvc\Component;

use ArrayAccess;
use Exception;
use Sledgehammer\Core\Html;
use Sledgehammer\Mvc\Import;

/**
 * Generate and import a Form.
 *
 * $form = new Form([new Input(['name' => 'field')]);
 * $data = $form->import()
 */
class Form extends Element implements Import, ArrayAccess
{
    /**
     * The <legend>.
     *
     * @var string
     */
    public $legend;

    /**
     * @var Import[]
     */
    public $fields = [];

    /**
     * @var Component[]
     */
    public $actions = [];

    /**
     * Wrap the field in a <fieldset>.
     *
     * @var bool
     */
    public $fieldset = true;

    /**
     * @var string
     */
    public $tag = 'form';

    /**
     * @var array
     */
    protected $attributes = [
        'method' => 'post',
    ];

    /**
     * @var string 'UNKNOWN', 'NOT_SENT', 'SENT'
     */
    protected $state = 'UNKNOWN';

    public function __construct($options)
    {
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->$option = $value;
            } elseif (is_object($value)) {
                $this->fields[$option] = $value;
            } elseif (is_int($option)) {
                $this->setAttribute($value, true);
            } else {
                $this->setAttribute($option, $value);
            }
        }
    }

    public function getValue()
    {
        $data = [];
        foreach ($this->fields as $key => $field) {
            $data[$key] = $field->getValue();
        }

        return $data;
    }

    public function setValue($values)
    {
        foreach ($values as $field => $value) {
            $this->fields[$field]->setValue($value);
        }
    }

    public function import(&$errors = null, $request = null)
    {
        $this->state = 'NOT_SENT';
        if ($request === null) {
            if (strtolower($this->getAttribute('method')) === 'post') {
                $request = $_POST;
            } elseif (strtolower($this->getAttribute('method')) === 'get') {
                $request = $_GET;
            } else {
                \Sledgehammer\notice('Unexpected method: "'.$this->getAttribute('method').'"');
                $request = $_REQUEST;
            }
        }
        if (count($request) == 0) {
            return $this->getValue();
        }
        $this->state = 'SENT';
        $data = [];
        foreach ($this->fields as $key => $field) {
            if (is_object($field) && method_exists($field, 'import')) {
                $data[$key] = $field->import($error, $request);
                if ($error) {
                    $errors[$key] = $error;
                }
            }
        }
        if (empty($data['actions'])) {
            // import which input type="submit" is sent.
            foreach ($this->actions as $component) {
                if (is_object($component) && method_exists($component, 'import')) {
                    if (empty($data['actions'])) {
                        $data['actions'] = [];
                    }
                    $data['actions'][$key] = $component->import($_, $request);
                }
            }
        }

        return $data;
    }

    public function isSent()
    {
        if ($this->state === 'UNKNOWN') {
            throw new Exception('Form->isSent() must be called *after* Form->import()');
        }

        return $this->state === 'SENT';
    }

    public function renderContents()
    {
        echo "\n";
        if ($this->fieldset) {
            echo "<fieldset>\n";
            if ($this->legend !== null) {
                echo "\t<legend>", Html::escape($this->legend), "</legend>\n";
            }
        }

        // Render form fields
        foreach ($this->fields as $label => $field) {
            echo "\t";
            render($field);
            echo "\n";
        }
        if ($this->fieldset) {
            echo "</fieldset>\n";
        }
        // Render form actions
        if (count($this->actions) !== 0) {
            foreach ($this->actions as $key => $component) {
                render($component);
            }
        }
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->fields);
    }

    public function offsetGet($offset)
    {
        return $this->fields[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->fields[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->fields[$offset]);
    }
}
