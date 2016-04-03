<?php

namespace Sledgehammer\Mvc\Component;

use Exception;
use Sledgehammer\Core\Html;
use Sledgehammer\Core\Object;
use Sledgehammer\Mvc\Component;

/**
 * Baseclass for components based on an html element.
 */
class Element extends Object implements Component
{
    /**
     * Element type: "div", "span", etc.
     *
     * @var string
     */
    public $tag = 'div';

    /**
     * Attributes for the html element.
     * (Keys are lowercase).
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Constructor.
     *
     * @param array $options Array containing properties and attribute values
     */
    public function __construct($options = [])
    {
        // Extract attributes override
        if (array_key_exists('attributes', $options)) {
            $this->attributes = $options['attributes'];
            unset($options['attributes']);
        }
        // Set properties and attributes
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->$option = $value;
            } else {
                if (is_int($option)) {
                    $this->setAttribute($value, true);
                } else {
                    $this->setAttribute($option, $value);
                }
            }
        }
    }

    /**
     * Render the element(s).
     */
    public function render()
    {
        if (method_exists($this, 'renderContents')) {
            echo Html::element($this->tag, $this->attributes, true);
            $this->renderContents();
            echo '</', $this->tag, '>';
        } else {
            echo Html::element($this->tag, $this->attributes);
        }
    }

    public function __toString()
    {
        try {
            return \Sledgehammer\component_to_string($this);
        } catch (Exception $e) {
            \Sledgehammer\report_exception($e);

            return '';
        }
    }

    // jQuery interface

    /**
     * Get the value of an attribute or set one or more attributes.
     *
     * @param string $attribute
     * @param mixed  $value     optional
     *
     * @return this|Element|mixed
     */
    public function attr($attribute, $value = null)
    {
        if (func_num_args() === 1) {
            if (is_array($attribute) == false) {
                return $this->getAttribute($attribute); // Get the value
            }
        } else {
            $attribute = array($attribute => $value);
        }
        foreach ($attribute as $name => $value) {
            if (is_int($name)) {
                $this->setAttribute($value, true);
            } else {
                if ($value === '') {
                    $this->removeAttribute($name);
                } else {
                    $this->setAttribute($name, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Adds the specified class(es) to the element.
     *
     * @param string|array $class One or more space-separated classes to be added to the class attribute.
     *
     * @return this|Element
     */
    public function addClass($class)
    {
        if (is_string($class) && strpos(trim($class), ' ')) {
            $class = explode(' ', $class);
        }
        if (is_array($class) === false) {
            $class = array($class);
        }
        $classes = explode(' ', trim($this->getAttribute('class')));
        foreach ($class as $name) {
            if (in_array($name, $classes) == false) {
                $classes[] = $name; // Add class
            }
        }
        $this->setAttribute('class', trim(implode(' ', $classes)));

        return $this;
    }

    /**
     * Determine whether the element is assigned the given class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function hasClass($class)
    {
        $classes = explode(' ', trim($this->getAttribute('class')));

        return in_array($class, $classes);
    }

    /**
     * Remove a single class, multiple classes, or all classes from the element.
     *
     * @param string|array $class
     *
     * @return this|Element
     */
    public function removeClass($class)
    {
        if (is_string($class) && strpos(trim($class), ' ')) {
            $class = explode(' ', $class);
        }
        if (is_array($class) === false) {
            $class = array($class);
        }
        $classes = explode(' ', trim($this->getAttribute('class')));
        foreach ($class as $name) {
            $key = array_search($name, $classes);
            if ($key !== false) {
                unset($classes[$key]); // Remove class
            }
        }
        $this->setAttribute('class', trim(implode(' ', $classes)));

        return $this;
    }

    /**
     * Add or remove one or more classes from each element in the set of matched elements, depending on either the class’s presence or the value of the switch argument.
     *
     * @param string $class
     *
     * @return this|Element
     */
    public function toggleClass($class)
    {
        if (is_string($class) && strpos(trim($class), ' ')) {
            $class = explode(' ', $class);
        }
        if (is_array($class) === false) {
            $class = array($class);
        }
        foreach ($class as $name) {
            if ($this->hasClass($name)) {
                $this->removeClass($name);
            } else {
                $this->addClass($name);
            }
        }

        return $this;
    }

    // DOM Interface https://developer.mozilla.org/en-US/docs/Web/API/element

    /**
     * Returns the value of the named attribute on the element.
     * If the named attribute does not exist, the value returned will be null.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getAttribute($name)
    {
        $key = strtolower($name);
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return;
    }

    /**
     * Adds a new attribute or changes the value of an existing attribute on the element.
     *
     * @param string $name  The name of the attribute.
     * @param mixed  $value The desired new value of the attribute
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[strtolower($name)] = $value;
    }

    /**
     * Removes an attribute from the element.
     *
     * @param string $name
     */
    public function removeAttribute($name)
    {
        unset($this->attributes[strtolower($name)]);
    }

    /**
     * Returns a boolean value indicating whether the specified element has the specified attribute or not.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute($name)
    {
        $key = strtolower($name);

        return array_key_exists($this->attributes[$key]) && $this->attributes[$key] !== null;
    }
}
