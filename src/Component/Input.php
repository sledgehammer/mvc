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

    public function __construct($options)
    {
        if (is_string($options)) {
            $options = ['name' => $options];
        }
        parent::__construct($options);
    }

    public function setValue($value)
    {
        if ($this->getAttribute('type') === 'checkbox') {
            $valueAttr = $this->getAttribute('value');
            if ($valueAttr && is_bool($value)) {
                $this->setAttribute('checked', $value);
            } elseif ($valueAttr) {
                $this->setAttribute('checked', $value === $valueAttr);
            } else {
                $this->setAttribute('checked', (bool)$value);
            }
            return $this->booleanAttribute('checked'); // return true or false
        }
        $this->setAttribute('value', $value);
    }

    public function getValue()
    {
        if ($this->getAttribute('type') === 'checkbox') {
            $valueAttr = $this->getAttribute('value');
            if ($valueAttr) {
                return $this->booleanAttribute('checked') ? $valueAttr : null; // return the value or null
            }
            return $this->booleanAttribute('checked'); // return true or false
        }
        return $this->getAttribute('value');
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
                $file = $_FILES[$name]; // @todo support for multiple files
                if ($file['error'] === UPLOAD_ERR_OK) {
                    unset($file['error']);
                } else {
                    $error = 'UPLOAD_FAILED';
                    $constants = get_defined_constants();
                    foreach ($constants as $constant => $constant_value) {
                        if (substr($constant, 0, 7) === 'UPLOAD_' && $constant_value === $file['error']) {
                            $error = $constant;
                            break;
                        }
                    }
                }
                break;

            case 'checkbox':
                $valueAttr = $this->getAttribute('value');
                $this->setAttribute('checked', false);
                if (\Sledgehammer\extract_element($request, $name, $value)) {
                    if ($valueAttr) {
                        $this->setAttribute('checked', $value === $valueAttr);
                    } else {
                        $this->setAttribute('checked', true);
                    }
                }
                break;

            default:
                if (\Sledgehammer\extract_element($request, $name, $value)) {
                    $this->setAttribute('value', $value);
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
            if (in_array(strtolower($this->getAttribute('type')), ['checkbox', 'radio'])) {
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
                unset($attributes['options'], $attributes['value']);
                echo Html::element($this->tag, $attributes, true);
                $selected = $this->getAttribute('value');
                $isIndexed = \Sledgehammer\is_indexed($options);
                foreach ($options as $value => $label) {
                    $option = [];
                    if ($isIndexed) {
                        $value = $label;
                    } else {
                        $option['value'] = $value;
                    }
                    if (\Sledgehammer\equals($value, $selected)) {
                        $option['selected'] = 'selected';
                    }
                    echo Html::element('option', $option, Html::escape($label));
                }
                echo '</select>';
                break;

            case 'textarea':
                unset($attributes['value']);
                echo Html::element($this->tag, $attributes, Html::escape($this->getAttribute('value')));
                break;

            default:
                echo Html::element($this->tag, $attributes);
                break;
        }
    }
}
