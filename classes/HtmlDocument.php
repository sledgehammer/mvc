<?php
/**
 * HtmlDocument
 */

namespace Sledgehammer;

/**
 * The container for generating html pages.
 *
 * @package MVC
 */
class HtmlDocument extends Object implements Document {

    /**
     * The doctype template. "html5" or "ajax"
     * @see "mvc/templates/doctype/" for details
     * @var string
     */
    public $doctype;

    /**
     * The value of the "Content-Type" header.
     * Defaults to "text/html; charset=utf-8"
     * @var string
     */
    public $contentType;

    /**
     * @var View
     */
    public $content;

    /**
     * When true the statusbar is rendered. (Defaults to ErrorHandler->html setting)
     * @var bool
     */
    public $statusbarVisible;

    /**
     * Configure tags in the <head> section.
     *
     * title, // Contents of the <title> tag
     * css = [], // Stylesheet urls
     * script = [], // Javascript urls (Beware: Placing scripts in the <head> hurts performance)
     *
     * meta = [], // The <meta> tags
     * link = [], // The <link> tags
     * base = string, // The <base href=""> tag
     * html = []; // Attributes for the <html> tag. Example: ['ng-app' => 'app']
     * body = []; // Attributes for the <body> tag. Example: ['class' => 'no-js']
     * @var array
     */
    private $headers;

    function __construct($doctype = 'html5') {
        $this->doctype = $doctype;
        $this->contentType = 'text/html; charset=' . strtolower(Framework::$charset);
        $this->statusbarVisible = Framework::$errorHandler->html; // Als er html error getoond mogen worden, toon dan ook de statusbalk.
    }

    /**
     * Vraag de headers op en werk de interne headers array bij.
     * @return array
     */
    function getHeaders() {

        $headers = [
            'http' => [
                'Content-Type' => $this->contentType,
            ],
            'html' => [],
            'body' => [],
        ];

        if (defined('WEBPATH') && WEBPATH != '/' && file_exists(PATH . 'application/public/favicon.ico')) {
            $headers['link']['favicon'] = ['rel' => 'shortcut icon', 'href' => WEBROOT . 'favicon.ico', 'type' => 'image/x-icon'];
        }
        if ($this->statusbarVisible) {
            $headers['css']['debug'] = WEBROOT . 'core/css/debug.css';
        }
        $this->headers = merge_headers($headers, $this->content);
        if (empty($this->headers['title'])) {
            notice('getHeaders() should contain a "title" element for a HTMLDocument');
        }
        return $this->headers;
    }

    /**
     * Generate the HTML document.
     *
     * @return void
     */
    function render() {
        if ($this->headers == null) {
            notice(get_class($this) . '->getHeaders() should be executed before ' . get_class($this) . '->render()');
        }
        $variables = [
            'title' => array_value($this->headers, 'title'),
            'head' => [],
            'html' => implode_xml_attributes($this->headers['html']),
            'body' => implode_xml_attributes($this->headers['body']),
            'content' => $this->content,
            'statusbarVisible' => $this->statusbarVisible,
        ];

        $validHeaders = ['http', 'title', 'base', 'css', 'meta', 'link', 'javascript', 'html', 'body'];
        foreach ($this->headers as $key => $value) {
            if (!in_array($key, $validHeaders)) {
                notice('Invalid header: "' . $key . '", expecting "' . human_implode('" or "', $validHeaders, '", "') . '"');
            }
        }

        // Build tags for the <head> section
        $head = [
            'base' => [],
            'meta' => [],
            'link' => [],
        ];
        if (isset($this->headers['base'])) {
            $head['base'][] = ['href' => $this->headers['base']];
        }
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
            foreach ($tags as $attributes) {
                $variables['head'][] = '<' . $tag . implode_xml_attributes($attributes) . '>';
            }
        }
        if (isset($this->headers['script'])) {
            foreach ($this->headers['script'] as $identifier => $url) {
                if (is_int($identifier)) {
                    $identifier = $url;
                }
                ob_start();
                javascript_once($url, $identifier);
                $variables['head'][] = ob_get_clean();
            }
        }
        $template = new Template('doctype/' . $this->doctype . '.php', $variables);
        $template->render();
    }

    function isDocument() {
        return true;
    }

}
