<?php
/**
 * Een Component die waarvan de $data onbehandeld ge-renderd wordt.
 *
 * @package MVC
 */

class HTML extends Object implements Component {

	public
		$data,
		$headers;

	function __construct($data, $headers = array()) {
		$this->data = $data; // waarde instellen.
		$this->headers = $headers;
	}

	/**
	 * @return array
	 */
	function getHeaders() {
		return $this->headers;
	}

	/**
	 * De $data echo-en
	 *
	 * @return void
	 */
	function render() {
		echo $this->data;
	}

}
?>
