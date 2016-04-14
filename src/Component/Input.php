<?php

namespace Sledgehammer\Mvc\Component;

use Sledgehammer\Core\Html;
use Sledgehammer\Mvc\Import;

/**
 * An <input>, <textarea> or <select> element.
 */
class Input extends Element implements Import
{
    /**
     * Prepends a <label> when set.
     *
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $tag = 'input';

    /**
     * @var mixed
     */
    protected $value;

    public function __construct($options)
    {
        if (is_string($options)) {
            $options = ['name' => $options];
        }
        parent::__construct($options);
        if ($this->tag === 'input' && empty($this->attributes['type'])) {
            $this->attributes['type'] = 'text';
        }
        if ($this->getAttribute('type') === 'checkbox') {
            if ($this->value !== null) {
                $this->attributes['value'] = $this->value;
                $this->value = $this->booleanAttribute('checked') ? $this->attributes['value'] : null;
            } else {
                $this->value = $this->booleanAttribute('checked');
            }
        }
    }

    public function setValue($value)
    {
        if ($this->attributes['type'] === 'checkbox' && is_bool($value)) {
            if ($this->hasAttribute('value')) {
                $this->value = $value ? $this->getAttribute('value') : null;
            } else {
                $this->value = $value;
            }
        } else {
            $this->value = $value;
        }
    }

    public function getValue()
    {
        return $this->value;
    }

    public function import(&$error = null, $request = null)
    {
        if ($request === null) {
            $request = $_REQUEST;
        }
        $name = $this->getAttribute('name');
        if ($name === null) {
            return;
        }
        $required = $this->hasAttribute('required');
        if ($required && $this->getAttribute('required') === false) {
            $required = false;
        }
        switch (strtolower($this->getAttribute('type'))) {

            case 'file':
                // Import a file upload
                if ($required) {
                    if (count($_FILES) == 0) {
                        notice('$_FILES is empty, check for <form enctype="multipart/form-data">');

                        return;
                    }
                    if (array_key_exists($name, $_FILES) == false) {
                        $error = 'MISSING_FILE';

                        return;
                    }
                }
                $this->value = $_FILES[$name]; // @todo support for multiple files
                if ($this->value['error'] === UPLOAD_ERR_OK) {
                    unset($this->value['error']);
                } else {
                    $error = 'UPLOAD_FAILED';
                    $constants = get_defined_constants();
                    foreach ($constants as $constant => $constant_value) {
                        if (substr($constant, 0, 7) === 'UPLOAD_' && $constant_value === $this->value['error']) {
                            $error = $constant;
                            break;
                        }
                    }
                }
                break;

            case 'checkbox':
                if (\Sledgehammer\extract_element($request, $name, $value)) {
                    $this->setValue(true);
                } else {
                    $this->setValue(false);
                }
                break;

            default:
                if (\Sledgehammer\extract_element($request, $name, $value)) {
                    $this->setValue($value);
                    if ($required && $value === '') {
                        $error = 'EMPTY_REQUIRED_FIELD';
                    }

                    return $value;
                }
                if ($required) {
                    $error = 'MISSING_FIELD';
                }
        }

        return $this->getValue();
    }

    public function render()
    {
        if ($this->label === null) {
            $this->renderElement();
        } else {
            if (in_array(strtolower($this->getAttribute('type')), array('checkbox', 'radio'))) {
                echo '<label>';
                $this->renderElement();
                echo '&nbsp;', Html::escape($this->label), '</label>';
            } else {
                echo '<label>', Html::escape($this->label), '</label>';
                $this->renderElement();
            }

            return;
        }
    }

    protected function renderElement()
    {
        $type = strtolower($this->getAttribute('type'));
        if (in_array($type, ['select', 'textarea'])) {
            $this->tag = $type;
            unset($this->attributes['type']);
        }
        $attributes = $this->attributes;

        switch ($this->tag) {
            case 'select':
                $options = $attributes['options'];
                unset($attributes['options']);
                echo Html::element($this->tag, $attributes, true);
                $isIndexed = \Sledgehammer\is_indexed($options);
                foreach ($options as $value => $label) {
                    $option = array();
                    if ($isIndexed) {
                        $value = $label;
                    } else {
                        $option['value'] = $value;
                    }
                    if (\Sledgehammer\equals($value, $this->value)) {
                        $option['selected'] = 'selected';
                    }
                    echo Html::element('option', $option, Html::escape($label));
                }
                echo '</select>';
                break;

            case 'textarea':
                echo Html::element($this->tag, $attributes, Html::escape($this->value));
                break;

            case 'checkbox':
                if ($this->value === false || $this->value === null) {
                    $attributes['checked'] = false;
                } else {
                    $attributes['checked'] = true;
                }
                echo Html::element($this->tag, $attributes);
                break;

            default:
                if ($this->value !== null) {
                    $attributes['value'] = $this->value;
                }
                echo Html::element($this->tag, $attributes);
                break;
        }
    }

    public function getAttribute($name)
    {
        if (isset($this->attributes['type']) && $this->attributes['type'] === 'checkbox') {
            if (strtolower($name) === 'checked') {
                return $this->value;
            }
        } elseif (strtolower($name) === 'value') {
            return $this->value;
        }

        return parent::getAttribute($name);
    }

    public function setAttribute($name, $value)
    {
        if (isset($this->attributes['type']) && $this->attributes['type'] === 'checkbox') {
            if (strtolower($name) === 'checked') {
                if (is_bool($value)) {
                    $this->value = $value;
                } else {
                    $this->value = (strtolower($value) === 'checked');
                }

                return;
            }
        } elseif (strtolower($name) === 'value') {
            $this->value = $value;

            return;
        }
        parent::setAttribute($name, $value);
    }
}
