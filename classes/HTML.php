<?php
/**
 * A view that outputs raw html.
 *
 * @package MVC
 */
namespace SledgeHammer;

class HTML extends Object implements View {

	/**
	 * @var string $html
	 */
	private $html;

	/**
	 * @var array Headers
	 */
	private $headers;

	/**
	 *
	 * @param string $html
	 * @param array $headers
	 */
	function __construct($html, $headers = array()) {
		$this->html = $html;
		$this->headers = $headers;
	}

	/**
	 * Output the $html
	 *
	 * @return void
	 */
	function render() {
		echo $this->data;
	}

	function getHeaders() {
		return $this->headers;
	}

}

?>
