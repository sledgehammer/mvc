<?php
/**
 * ViewHeaders.
 */

namespace Sledgehammer\Mvc;

use Sledgehammer\Core\Object;

/**
 * Add Headers to any View.
 * Makes it possible to add headers from a Controller without adding a headers to all View classes.
 */
class ViewHeaders extends Object implements View
{
    /**
     * @var View
     */
    private $view;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var bool
     */
    private $overrideHeaders;

    /**
     * @param View  $view
     * @param array $headers
     * @param bool  $overrideHeaders Bij false zullen de headers van het component leidend zijn.
     */
    public function __construct($view, $headers, $overrideHeaders = false)
    {
        $this->view = $view;
        $this->headers = $headers;
    }

    public function getHeaders()
    {
        if ($this->overrideHeaders == false) {
            return \Sledgehammer\merge_headers($this->headers, $this->view); //  standaard merge
        }
        // De headers van dit object zijn leidend.
        if (method_exists($this->view, 'getHeaders')) {
            return \Sledgehammer\merge_headers($this->view->getHeaders(), $this->headers);
        }

        return $this->headers; // Het component had geen headers.
    }

    public function render()
    {
        if (\Sledgehammer\is_valid_view($this->view)) {
            $this->view->render();
        }
    }

    public function isDocument()
    {
        if (method_exists($this->view, 'isDocument')) {
            return $this->view->isDocument();
        }

        return false;
    }
}
