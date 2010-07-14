<?php
/**
 * Een typische html/xhtml pagina 
 *
 * @package MVC
 */

class HTMLDocument extends Document {

	public
		$doctype,
		$statusbar, // bool  Bepaald of de statusbalk getoond word. (Wordt automatisch bepaald door de ErrorHandler->html waarde)
		// Tags die in de <head> vallen
		$title, // De <title> tag
		$stylesheets = array(), // De stylesheet urls
		$javascripts = array(), // De javascript urls (Bij voorkeur geen javascript in de head.)

		// Voor specifieke attibuten in de head tags zijn er deze:
		$meta_tags = array(), // De <meta> tags
		$link_tags = array(), // De <link> tags 
		$script_tags = array(), // De <script> tags

		$bodyParameters = array(); // parameters die binnen de <body> tag geplaatst worden

	function __construct($doctype = 'xhtml', $charset = 'UTF-8') {
		parent::__construct($charset);
		$this->doctype = $doctype;
		$this->statusbar = $GLOBALS['ErrorHandler']->html; // Als er html error getoond mogen worden, toon dan ook de statusbalk.
		$this->headers['mimetype'] = 'Content-Type: text/html; charset='.strtolower($charset);
	}

	/**
	 * Het document genereren
	 *
	 * @return void
	 */
	function render() {
		$variables = array(
			'charset' => $this->charset,
			'title' => $this->title,
			'headerTags' => array(),
			'bodyParameters' => implode_xml_parameters($this->bodyParameters, $this->charset),
			'body' => $this->component,
			'statusbar' => $this->statusbar
		);
		// tags binnen de <head> instellen
		$head = array(
			'meta' => $this->meta_tags,
			'link' => $this->link_tags,
		);
		$eot = ($this->doctype === 'xhtml') ? ' />' : '>'; // End of Tag instellen
		foreach ($this->stylesheets as $url) {
			$head['link'][] = array('rel' => 'stylesheet', 'href' => $url, 'type' => 'text/css');
		}
		foreach ($head as $tag => $tags) {
			foreach ($tags as $parameters) {
				$variables['headerTags'][] = '<'.$tag.implode_xml_parameters($parameters, $this->charset).$eot;
			}
		}
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
		}
		$this->component = new Template('doctype.'.$this->doctype, $variables);
		parent::render();
	}
}
?>
