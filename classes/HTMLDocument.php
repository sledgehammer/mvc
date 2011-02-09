<?php
/**
 * Een typische html/xhtml pagina 
 *
 * @package MVC
 */

class HTMLDocument extends Object implements Document {

	public $doctype;
	
	/**
	 * @var Component
	 */
	public $content;
	/**
	 * Bepaald of de statusbalk getoond word. (Wordt automatisch bepaald door de ErrorHandler->html waarde)
	 * @var bool
	 */
	public $showStatusbar;

		// Tags die in de <head> vallen
		/*
		$title, // De <title> tag
		$stylesheets = array(), // De stylesheet urls
		$javascripts = array(), // De javascript urls (Bij voorkeur geen javascript in de head.)

		// Voor specifieke attibuten in de head tags zijn er deze:
		$meta_tags = array(), // De <meta> tags
		$link_tags = array(), // De <link> tags 
		$script_tags = array(), // De <script> tags

		$bodyParameters = array(); // parameters die binnen de <body> tag geplaatst worden
*/
	private $headers;

	function __construct($doctype = 'xhtml') {
		//parent::__construct($charset);
		$this->doctype = $doctype;
		$this->showStatusbar = $GLOBALS['ErrorHandler']->html; // Als er html error getoond mogen worden, toon dan ook de statusbalk.
		
	}

	/**
	 *
	 * @return array
	 */
	function  getHeaders() {
		$headers = array(
			'http' => array(
				'Content-Type' => 'text/html; charset='.strtolower($GLOBALS['charset']),
			),
			'charset' => $GLOBALS['charset'],
			'bodyParameters' => array()
		);

		if (defined('WEBPATH') && WEBPATH != '/' && file_exists(PATH.'application/public/favicon.ico')) {
			$headers['link']['favicon'] = array('rel' => 'shortcut icon', 'href' => WEBROOT.'favicon.ico', 'type' => 'image/x-icon');
		}
		// $headers['http']['Content-Type'] = 'application/xhtml+xml';
		if ($GLOBALS['ErrorHandler']->html) {
			$headers['css']['debug'] = WEBROOT.'core/stylesheets/debug.css';
		}
		$this->headers = merge_headers($headers, $this->content);
		if (empty($this->headers['title'])) {
			notice('getHeaders() should contain a "title" element for a HTMLDocument');
		}
		return $this->headers;
	}

	/**
	 * Het document genereren
	 *
	 * @return void
	 */
	function render() {
		if ($this->headers == null) {
			notice(get_class($this).'->getHeaders() should be executed before '.get_class($this).'->render()');
		}
		$variables = array(
			'charset' => $this->headers['charset'],
			'title' => array_value($this->headers, 'title'),
			'head' => array(),
			'bodyParameters' => implode_xml_parameters($this->headers['bodyParameters']),
			'body' => $this->content,
			'showStatusbar' => $this->showStatusbar,
		);
		
		$validHeaders = array('http', 'title', 'charset', 'css', 'meta', 'link', 'bodyParameters');
		foreach ($this->headers as $key => $value) {
			if (!in_array($key, $validHeaders)) {
				notice('Invalid header: "'.$key.'", expecting "'.human_implode('" or "', $validHeaders, '", "').'"');
			}
		}
		
		// tags binnen de <head> instellen
		$head = array(
			'meta' => array(),
			'link' => array(),
		);
		if (isset($this->headers['meta'])) {
			$head['meta'] = $this->headers['meta'];
		}
		if (isset($this->headers['link'])) {
			$head['link'] = $this->headers['link'];
		}
		if (isset($this->headers['css'])) {
			foreach ($this->headers['css'] as $url) {
				$head['link'][] = array('href' => $url, 'type' => 'text/css', 'rel' => 'stylesheet');
			}
		}
		$eot = ($this->doctype === 'xhtml') ? ' />' : '>'; // End of Tag instellen
		foreach ($head as $tag => $tags) {
			foreach ($tags as $parameters) {
				$variables['head'][] = '<'.$tag.implode_xml_parameters($parameters).$eot;
			}
		}
		/*
		$scripts = $this->script_tags;
		foreach ($this->javascripts as $identifier => $url) {
			if (is_int($identifier)) {
				$scripts[] = array('src' => $url, 'type' => 'text/javascript');
			} else {
				$scripts[$identifier] = array('src' => $url, 'type' => 'text/javascript');
			}
		}
		
		foreach ($scripts as $identifier => $parameters) {
			if (is_int($identifier)) {
				$identifier = $parameters['src'];
			}
			$GLOBALS['included_javascript'][$identifier] = true;
			if (isset($parameters['type'])  && isset($parameters['src']) && $parameters['type'] == 'text/javascript') { // Het het om een javascript bestand?
			}
			$variables['headerTags'][] = '<script'.implode_xml_parameters($parameters).'></script>';
		}*/
		
		$template = new Template('doctype.'.$this->doctype, $variables);
		$template->render();
	}
	
	function isDocument() {
		return true;
	}
}
?>
