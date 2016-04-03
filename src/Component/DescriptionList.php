<?php

namespace Sledgehammer\Mvc\Component;

use Sledgehammer\Core\Html;

/**
 * A Description list <dl><dt><dd></dl>.
 *
 * Tip: Use 'class' => 'dl-horizontal' for a different layout
 */
class DescriptionList extends Element
{
    /**
     * @var array
     */
    public $items = array();

    /**
     * @var string
     */
    public $tag = 'dl';

    public function renderContents()
    {
        foreach ($this->items as $label => $values) {
            echo "\t";
            echo Html::element('dt', array(), $label);
            if (is_array($values)) {
                foreach ($values as $value) {
                    echo Html::element('dd', array(), $value);
                }
            } else {
                echo Html::element('dd', array(), $values);
            }
        }
    }
}
