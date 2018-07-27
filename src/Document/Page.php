<?php

namespace Sledgehammer\Mvc\Document;

use Sledgehammer\Core\Debug\ErrorHandler;
use Sledgehammer\Core\Framework;
use Sledgehammer\Core\Base;
use Sledgehammer\Mvc\Component;
use Sledgehammer\Mvc\Document;
use Sledgehammer\Mvc\Component\Template;

/**
 * The container for generating html pages.
 */
class Page extends Base implements Document
{
    /**
     * @var Component
     */
    public $content;

    /**
     * Bepaald of de statusbalk getoond word. (Wordt automatisch bepaald door de ErrorHandler->html waarde).
     *
     * @var bool
     */
    public $statusbar = false;

    /**
     *  Tags die in de <head> vallen.
     *
     * title, // De <title> tag
     * css = array(), // De stylesheet urls
     * javascript = array(), // De javascript urls (Bij voorkeur geen javascript in de head.)
     *
     * meta = array(), // De <meta> tags
     * link = array(), // De <link> tags
     * htmlParameters = array(); // parameters die binnen de <html> tag geplaatst worden
     * bodyParameters = array(); // parameters die binnen de <body> tag geplaatst worden
     *
     * @var array
     */
    private $headers;

    /**
     * Vraag de headers op en werk de interne headers array bij.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = [
            'http' => [
                'Content-Type' => 'text/html; charset=utf-8',
            ],
            'charset' => 'UTF-8',
            'htmlParameters' => [],
            'bodyParameters' => [],
        ];
        if (defined('Sledgehammer\WEBPATH') && \Sledgehammer\WEBPATH != '/' && file_exists(\Sledgehammer\PATH.'application/public/favicon.ico')) {
            $headers['link']['favicon'] = ['rel' => 'shortcut icon', 'href' => \Sledgehammer\WEBROOT.'favicon.ico', 'type' => 'image/x-icon'];
        }
        if ($this->statusbar) {
            $headers['css']['debug'] = \Sledgehammer\WEBROOT.'core/css/debug.css';
        }
        $this->headers = \Sledgehammer\merge_headers($headers, $this->content);
        if (empty($this->headers['title'])) {
            notice('getHeaders() should contain a "title" element for a HTMLDocument');
        }

        return $this->headers;
    }

    /**
     * Het document genereren.
     */
    public function render()
    {
        if ($this->headers == null) {
            notice(get_class($this).'->getHeaders() should be executed before '.get_class($this).'->render()');
        }
        $variables = [
            'charset' => $this->headers['charset'],
            'title' => array_value($this->headers, 'title'),
            'head' => [],
            'htmlParameters' => \Sledgehammer\implode_xml_parameters($this->headers['htmlParameters']),
            'bodyParameters' => \Sledgehammer\implode_xml_parameters($this->headers['bodyParameters']),
            'body' => $this->content,
            'statusbar' => $this->statusbar,
        ];

        $validHeaders = ['http', 'title', 'charset', 'css', 'meta', 'link', 'javascript', 'htmlParameters', 'bodyParameters'];
        foreach ($this->headers as $key => $value) {
            if (!in_array($key, $validHeaders)) {
                notice('Invalid header: "'.$key.'", expecting "'.\Sledgehammer\human_implode('" or "', $validHeaders, '", "').'"');
            }
        }

        // tags binnen de <head> instellen
        $head = [
            'meta' => [],
            'link' => [],
        ];
        if (isset($this->headers['meta'])) {
            $head['meta'] = $this->headers['meta'];
        }
        if (isset($this->headers['link'])) {
            $head['link'] = $this->headers['link'];
        }
        if (isset($this->headers['css'])) {
            foreach ($this->headers['css'] as $url) {
                $head['link'][] = ['href' => $url, 'type' => 'text/css', 'rel' => 'stylesheet'];
            }
        }
        foreach ($head as $tag => $tags) {
            foreach ($tags as $parameters) {
                $variables['head'][] = '<'.$tag.\Sledgehammer\implode_xml_parameters($parameters).'>';
            }
        }
        if (isset($this->headers['javascript'])) {
            foreach ($this->headers['javascript'] as $identifier => $url) {
                if (is_int($identifier)) {
                    $identifier = $parameters['src'];
                }
                ob_start();
                javascript_once($url, $identifier);
                $variables['head'][] = ob_get_clean();
            }
        }
        $template = new Template('sledgehammer/mvc/templates/page.php', $variables);
        $template->render();
    }

    public function isDocument()
    {
        return true;
    }
}
