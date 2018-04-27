<?php

namespace Sledgehammer\Mvc\Component;

use Sledgehammer\Core\Html;
use Sledgehammer\Core\Singleton;

/**
 * Breadcrumb Navigation.
 */
class Breadcrumbs extends Element
{
    use Singleton;
    /**
     * Format: array(array('url' => url, 'label' => label)).
     *
     * @var array
     */
    public $crumbs = [];

    /**
     * @var string
     */
    public $tag = 'ul';
    /**
     * Attributes for the html element.
     *
     * @var array
     */
    protected $attributes = [
        'class' => 'breadcrumb',
    ];

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
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
     * @param string|array $crumb
     * @param string       $url
     */
    public function add($crumb, $url = null)
    {
        if (is_array($crumb) == false) {
            $crumb = [
                'label' => $crumb,
            ];
        }
        $crumb['url'] = $url;
        $this->crumbs[] = $crumb;
    }

    public function renderContents()
    {
        echo "\n";
        foreach ($this->crumbs as $crumb) {
            if ($crumb['url'] == false || \Sledgehammer\value($crumb['active'])) {
                echo "\t<li class=\"active\">";
            } else {
                echo "\t<li>";
            }
            if (isset($crumb['icon'])) {
                $label = Html::icon($crumb['icon']).' '.Html::escape($crumb['label']);
            } else {
                $label = Html::escape($crumb['label']);
            }
            if ($crumb['url']) {
                echo Html::element('a', ['href' => $crumb['url']], $label);
            } else {
                echo $label;
            }
            echo "</li>\n";
        }
    }
}
