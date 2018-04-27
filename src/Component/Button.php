<?php

namespace Sledgehammer\Mvc\Component;

use Sledgehammer\Core\Html;

/**
 * A button element.
 */
class Button extends Element
{
    /**
     * @var string
     */
    public $tag = 'button';

    /**
     * @var string
     */
    public $icon;

    /**
     * @var string
     */
    public $label;

    /**
     * Constructor.
     *
     * @param string|array $label_or_options
     * @param array        $options
     */
    public function __construct($label_or_options, $options = [])
    {
        if (is_array($label_or_options) === false) {
            $options['label'] = $label_or_options;
        } else {
            if (count($options) !== 0) {
                \Sledgehammer\notice('Second parameter $options is ignored');
            }
            $options = $label_or_options;
        }
        // Set attributes and properties
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->$option = $value;
            } else {
                $this->attributes[$option] = $value;
            }
        }
    }

    public function render()
    {
        if ($this->icon) {
            $label = Html::icon($this->icon).'&nbsp;'.Html::escape($this->label);
        } else {
            $label = Html::escape($this->label);
        }
        echo Html::element($this->tag, $this->attributes, $label);
    }

    public function __toString()
    {
        return \Sledgehammer\component_to_string($this);
    }
}
