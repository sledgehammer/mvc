<?php

namespace Sledgehammer\Mvc\Component;

use Sledgehammer\Core\Base;
use Sledgehammer\Mvc\Component;

/**
 * Add Headers to any Component.
 * Makes it possible to add headers from a Controller without adding a headers to all Component classes.
 */
class Headers extends Base implements Component
{
    /**
     * @var Component
     */
    private $component;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var bool
     */
    private $allowOverride;

    /**
     * @param Component $component
     * @param array     $headers
     * @param bool      $allowOverride Allow the component to override the given headers.
     */
    public function __construct($component, $headers, $allowOverride = false)
    {
        $this->component = $component;
        $this->headers = $headers;
        $this->allowOverride = $allowOverride;
    }

    public function getHeaders()
    {
        if (method_exists($this->component, 'getHeaders')) {
            if ($this->allowOverride) {
                return \Sledgehammer\merge_headers($this->headers, $this->component);
            }

            return \Sledgehammer\merge_headers($this->component->getHeaders(), $this->headers);
        }

        return $this->headers; // Het component had geen headers.
    }

    public function render()
    {
        if (\Sledgehammer\is_valid_component($this->component)) {
            $this->component->render();
        }
    }

    public function isDocument()
    {
        if (method_exists($this->component, 'isDocument')) {
            return $this->component->isDocument();
        }

        return false;
    }
}
