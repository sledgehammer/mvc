<?php
namespace Sledgehammer\Mvc\Component;

use Sledgehammer\Core\Html;
use Sledgehammer\Mvc\HtmlElement;
use Sledgehammer\Mvc\Import;


/**
 * An <input>, <textarea> or <select> element.
 */
class Input extends HtmlElement implements Import
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
            $options = array('name' => $options);
        }
        parent::__construct($options);
        if ($this->tag === 'input' && empty($this->attributes['type'])) {
            $this->attributes['type'] = 'text';
        }
    }

    public function initial($value)
    {
        $this->attributes['value'] = $value;
    }

    public function import(&$error, $request = null)
    {
        if ($request === null) {
            $request = $_REQUEST;
        }
        $name = $this->getAttribute('name');
        if ($name === null) {
            return; // De naam is niet opgegeven.
        }
        switch (strtolower($this->getAttribute('type'))) {

            case 'file':
                // Import a file upload
                if (count($_FILES) == 0) {
                    //					if (!array_key_exists('_FILES', $request)) {
//						return null; // Het formulier is nog niet gepost
//					}
                    notice('$_FILES is empty, check for <form enctype="multipart/form-data">');

                    return;
                }
                if (array_key_exists($name, $_FILES) == false) {
                    $error = 'Invalid name';

                    return;
                }
                $file = $_FILES[$name]; // @todo support for multiple files
                switch ($file['error']) {

                    case UPLOAD_ERR_OK:
                        unset($file['error']);

                        return $file;

                    case UPLOAD_ERR_NO_FILE:
                        // @todo Check if the input was required.
                        break;

                    case UPLOAD_ERR_INI_SIZE:
                        $error = 'De grootte van het bestand is groter dan de in php.ini ingestelde waarde voor upload_max_filesize';
                        break;

                    case UPLOAD_ERR_FORM_SIZE:
                        $error = 'De grootte van het bestand is groter dan de in html gegeven MAX_FILE_SIZE';
                        break;

                    case UPLOAD_ERR_PARTIAL:
                        $error = 'Het bestand is maar gedeeltelijk geupload';
                        break;

                    default:
                        $error = 'Unknown error: "'.$file['error'].'"';
                }

                return; // Er is geen (volledig) bestand ge-upload
                
            case 'checkbox':
                unset($this->attributes['checked']);
                $index = array_search('checked', $this->attributes);
                if ($index !== false) {
                    unset($this->attributes[$index]);
                }
                $valueAttr = $this->getAttribute('value');
                if (\Sledgehammer\extract_element($request, $name, $value)) {
                    $this->attributes[] = 'checked';
                    if ($valueAttr === null) {
                        return true;
                    }
                    return $value;
                }
                if ($valueAttr === null) {
                    return false;
                }
                break;

            default:
                if (\Sledgehammer\extract_element($request, $name, $value)) {
                    $this->attributes['value'] = $value;

                    return $value;
                }
                $error = 'Import failed';

                return;
        }
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
        if (in_array($type, array('select', 'textarea'))) {
            $this->tag = $type;
            unset($this->attributes['type']);
        }
        $attributes = $this->attributes;

        switch ($this->tag) {
            case 'select':
                $options = $attributes['options'];
                unset($attributes['options']);
                $selected = $this->getAttribute('value');
                unset($attributes['value']);
                echo Html::element($this->tag, $attributes, true);
                $isIndexed = \Sledgehammer\is_indexed($options);
                foreach ($options as $value => $label) {
                    $option = array();
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
