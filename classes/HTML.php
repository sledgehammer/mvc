<?php
/**
 * Een Component die waarvan de $data onbehandeld ge-renderd wordt.
 *
 * @package MVC
 */
namespace SledgeHammer;
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
