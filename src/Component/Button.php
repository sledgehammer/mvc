<?php

namespace Sledgehammer\Mvc\Component;

use Sledgehammer\Core\Html;
use Sledgehammer\Mvc\HtmlElement;

/**
 * A button (btn) element.
 */
class Button extends HtmlElement
{
    /**
     * @var string
     */
    public $tag = 'button';

    /**
     * Attributes for the html element.
     *
     * @var array
     */
    public $attributes = array(
        'class' => 'btn',
    );

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
    public function __construct($label_or_options, $options = array())
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
        return \Sledgehammer\view_to_string($this);
    }
}