<?php
/**
 * Een component die de data als een json string rendert
 * 
 * 
 * @package MVC
 */
namespace SledgeHammer;
class Json extends Object implements Document {
	
	/**
	 * @var mixed $data  De data die via een json_encode vestuurd word.
	 */ 
	public $data;

	/**
	 * 
	 * @param mixed $data  De data die via een json_encode vestuurd word.
	 */
	function __construct($data = null) {
		$this->data = $data;
	}

	/**
	 * 
	 */
	function getHeaders() {
		if (count($_FILES) == 0) {
			return array('http' => array(
				'Content-Type' => 'application/json',
			));
		}
		// Als er bestanden ge-upload zijn, gaat het *niet* om een XMLHttpRequest.
		// Een "application/json" header zal dan een ongewenste download veroorzaken.
		// (Of als de JSONView extensie is geinstalleerd, krijg je json versmurft als html)
	}
	
	/**
	 * Verstuur/echo de $data als json string.
	 */
	function render() {			
		echo json_encode($this->data);
	}

	/**
	 * Dit Component kan niet binnen een ander component getoond worden.
	 * 
	 * @return bool
	 */
	function isDocument() {
		return true;
	}  
}  
?>