<?php
/**
 * The MVC alternative to the redirect() function.
 *
 * Because redirect() function stops execution of the script, the flow of the MVC classes is interupted.
 * The Redirect class completes the MVC flow and send the headers via the Website->handleRequest()
 *
 * (Compatible with SledgeHammer\HttpServer)
 *
 * @package MVC
 */
namespace SledgeHammer;
class Redirect extends Object implements Document {

	private $url;
	private $permanently;

	function __construct($url, $permanently = false) {
		$this->url = $url;
		$this->permanently = $permanently;
	}

	function getHeaders() {
		return array(
			'http' => array(
				'Status' => ($this->permanently ? '301 Moved Permanently': '302 Found'),
				'Location' => $this->url
			)
		);
	}

	function isDocument() {
		return true;
	}

	function render() {
		// do nothing
	}
}
?>
