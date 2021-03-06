<?php

namespace Sledgehammer\Mvc\Component;

/**
 * A single alert message.
 */
class Alert extends Element
{
    /**
     * The alert body.
     *
     * @var string html
     */
    public $message;

    /**
     * Show a "X "to dismiss the alert.
     *
     * @var bool
     */
    public $close = false;

    /**
     * Attributes for the html element.
     *
     * @var array
     */
    public $attributes = [
        'class' => 'alert',
    ];

    /**
     * Constructor.
     *
     * @param string $message HTML
     * @param array  $options
     */
    public function __construct($message, $options = [])
    {
        $this->message = $message;
        // Set attributes and properties
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->$option = $value;
            } else {
                $this->attributes[$option] = $value;
            }
        }
    }

    /**
     * Render the element contents.
     */
    public function renderContents()
    {
        if ($this->close) {
            echo '<button class="close" data-dismiss="alert">&times</button>';
        }
        echo $this->message;
    }

    /**
     * Create an info alert.
     *
     * @param string $message HTML
     *
     * @return Alert
     */
    public static function info($message)
    {
        return new self($message, ['class' => 'alert alert-info']);
    }

    /**
     * Create an error alert.
     *
     * @param string $message HTML
     *
     * @return Alert
     */
    public static function error($message)
    {
        return new self($message, ['class' => 'alert alert-error']);
    }

    /**
     * Create a success alert.
     *
     * @param string $message HTML
     *
     * @return Alert
     */
    public static function success($message)
    {
        return new self($message, ['class' => 'alert alert-success']);
    }
}
