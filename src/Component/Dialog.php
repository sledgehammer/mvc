<?php

namespace Sledgehammer\Mvc\Component;

use Sledgehammer\Core\Object;
use Sledgehammer\Mvc\Component;

/**
 * A dialog popup with where selected choice is posted back the server.
 * Compatible with Bootrap 3 `.modal-dialog`.
 */
class Dialog extends Object implements Component
{
    public $template = 'sledgehammer/mvc/templates/dialog.php';
    private $title;
    private $body;
    private $choices;
    private $identifier = 'answer';
    private $method = 'post';
    private $close = false;

    /**
     * @param string $title
     * @param string $body    html
     * @param array  $choices
     * @param array  $options [optional]
     */
    public function __construct($title, $body, $choices = array(), $options = array())
    {
        $this->title = $title;
        $this->body = $body;
        $this->choices = $choices;
        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    public function prompt(&$error = null, $request = null)
    {
        if ($request === null) {
            $request = $_POST;
        }
        if (\Sledgehammer\extract_element($request, $this->identifier, $answer)) {
            // @todo check available options
            return $answer;
        }
    }

    public function getHeaders()
    {
        return array(
            'title' => $this->title,
        );
    }

    public function render()
    {
        render(new Template($this->template, get_object_vars($this)));
    }
}
