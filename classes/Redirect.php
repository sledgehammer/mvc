<?php
/**
 * De MVC variant op de redirect() functie.
 * De redirect() functie beeindig/exit() direct het script, terwijl dit Redirect object via de FrontController wordt afgehandeld.
 * En is daardoor compatible met HttpServer in de http_daemon module.
 *
 * @package MVC
 */
namespace SledgeHammer;
class Redirect extends Object implements Document {

	private $headers;

	function __construct($url, $permanently = false) {
		$this->headers = array(
			'Status' => ($permanently ? '301 Moved Permanently': '302 Found'),
			'Location' => $url
		);
	}

	function getHeaders() {
		return array(
			'http' => $this->headers
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
