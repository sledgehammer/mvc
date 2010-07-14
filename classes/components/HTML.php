<?php
/**
 * Een Component die waarvan de invoer onbehandeld ge-renderd wordt.
 *
 * @package MVC
 */

class HTML extends Object implements Component {

	public
		$data;

	function __construct($data) {
		$this->data = $data; // waarde instellen.
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
