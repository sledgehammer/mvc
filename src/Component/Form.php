<?php
namespace Sledgehammer\Mvc\Component;

use Sledgehammer\Core\Html;
use Sledgehammer\Mvc\HtmlElement;
use Sledgehammer\Mvc\Import;

/**
 * Generate and import a Form.
 */
class Form extends HtmlElement implements Import
{
    /**
     * @var string
     */
    public $legend;

    /**
     * @var array|Import[]
     */
    public $fields = [];

    /**
     * @var array
     */
    public $actions = [];

    /**
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
    protected $attributes = array(
        'method' => 'post',
    );

    public function __construct($options)
    {
        if (\Sledgehammer\is_indexed($options)) {
            $options = array('fields' => $options);
        }
        parent::__construct($options);
    }

    public function initial($values)
    {
        foreach ($values as $field => $value) {
            $this->fields[$field]->initial($value);
        }
    }

    public function import(&$errors, $request = null)
    {
        if ($request === null) {
            if (strtolower($this->getAttribute('method')) === 'post') {
                $request = $_POST;
            } elseif (strtolower($this->getAttribute('method')) === 'get') {
                $request = $_GET;
            } else {
                \Sledgehammer\notice('Invalid import method');
                $request = $_REQUEST;
            }
        }
        if (count($request) == 0) {
            $errors = false;
            return;
        }
        $data = [];
        foreach ($this->fields as $key => $field) {
            $data[$key] = $field->import($error, $request);
            if ($error) {
                $errors[$key] = $error;
            }
        }
        if (count($errors)) {
            return;
        }

        return $data;
    }

    public function renderContents()
    {
        echo "\n";
        if (\Sledgehammer\array_value($this->attributes, 'class') === 'form-horizontal') {
            $renderControlGroups = true;
        } else {
            $renderControlGroups = false;
        }
        if ($this->fieldset) {
            echo "<fieldset>\n";
            if ($this->legend !== null) {
                echo "\t<legend>", Html::escape($this->legend), "</legend>\n";
            }
        }

        // Render form fields
        foreach ($this->fields as $label => $field) {
            echo "\t";
            if ($renderControlGroups) {
                echo '<div class="control-group">';
                if (is_int($label) === false) {
                    echo '<label class="control-label">', Html::escape($label), '</label>';
                }
                echo '<div class="controls">';
                render($field);
                echo '</div></div>';
            } else {
                if (is_int($label) === false) {
                    echo '<label>', Html::escape($label), '</label>';
                }
                render($field);
            }
            echo "\n";
        }

        // Render form actions
        if (count($this->actions) !== 0) {
            echo '<div class="form-actions">';
            foreach ($this->actions as $name => $action) {
                if (is_string($name)) {
                    if (is_array($action) === false) {
                        $action = array(
                            'label' => $action,
                        );
                    }
                    $action['name'] = $name;
                }
                echo new Button($action);
            }
            echo '</div>';
        }
        if ($this->fieldset) {
            echo "</fieldset>\n";
        }
    }
}
