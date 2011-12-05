<?php
/**
 * Renders data as Json
 *
 * @package MVC
 */
namespace SledgeHammer;
class Json extends Object implements Document {

	/**
	 * @var mixed $data
	 */
	public $data;

	/**
	 * @param mixed $data
	 */
	function __construct($data = null) {
		$this->data = $data;
	}

	/**
	 * Change Content-Type to "application/json"
	 */
	function getHeaders() {
		if (count($_FILES) == 0) {
			return array('http' => array(
				'Content-Type' => 'application/json',
			));
		} else {
			return array('http' => array(
				'Content-Type' => 'plain/text',
			));
		}
		// Als er bestanden ge-upload zijn, gaat het *niet* om een XMLHttpRequest, maar waarschijnlijk om een upload naar een hidden iframe via javascript.
		// Een "application/json" header zal dan een ongewenste download veroorzaken.
		// (Of als de JSONView extensie is geinstalleerd, wordt de json versmurft als html)
	}

	/**
	 * Render the $data as json
	 */
	function render() {
		echo json_encode($this->data);
	}

	/**
	 * Render a standalone document
	 *
	 * @return bool
	 */
	function isDocument() {
		return true;
	}
}
?>