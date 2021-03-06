<?php

namespace Sledgehammer\Mvc\Component;

use Sledgehammer\Core\Base;
use Sledgehammer\Mvc\Component;

/**
 * Een component voor het weergeven van php-templates.
 * De templates zijn standaard php. er wordt geen gebruik gemaakt van een tempate engine zoals bv Smarty.
 */
class Template extends Base implements Component
{
    /**
     * Bestandsnaam van de template (exclusief thema map).
     *
     * @var string
     */
    public $template;

    /**
     * Variabelen die in de template worden gezet. Als je array('naam' => value) meegeeft kun in de template {$naam} gebruiken.
     *
     * @var array
     */
    public $variables;

    /**
     * De variable die gebruikt wordt voor de getHeaders().
     *
     * @var array
     */
    public $headers;

    public static $includePaths = [\Sledgehammer\VENDOR_DIR];

    /**
     * @param string $template
     * @param array  $variables
     * @param array  $headers
     */
    public function __construct($template, $variables = [], $headers = [])
    {
        $this->variables = $variables;
        $this->headers = $headers;

        if (file_exists($template)) { // file found?
            $this->template = $template;
        } elseif (substr($template, 0, 1) !== '/') { // A relative path?
            foreach (static::$includePaths as $folder) {
                if (file_exists($folder.$template)) { // file found?
                    $this->template = $folder.$template;
                    break;
                }
            }
        }
        if ($this->template === null) {
            \Sledgehammer\warning('Template: "'.$template.'" not found', ['includePaths' => static::$includePaths]);
            $this->template = $template;
        }
    }

    /**
     * Vraag de ingestelde headers op van deze template en eventuele subcomponenten.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = $this->headers;
        $components = $this->getSubviews($this->variables);
        foreach ($components as $component) {
            $headers = \Sledgehammer\merge_headers($headers, $component);
        }

        return $headers;
    }

    /**
     * De template parsen en weergeven.
     */
    public function render()
    {
        extract($this->variables);
        include $this->template;
    }

    private function getSubviews($array)
    {
        $views = [];
        foreach ($array as $element) {
            if (is_component($element)) {
                $views[] = $element;
            } elseif (is_array($element)) {
                $views = array_merge($views, $this->getSubviews($element));
            }
        }

        return $views;
    }
}
