<?php
/**
 * Een component die de data als een json string rendert
 * 
 * 
 * @package MVC
 */
class Json extends Object implements Component {
	
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
	 * Verstuur/echo de $data als json string.
	 */
	function render() {
		if (count($_FILES) > 0) {
			// Als er bestanden ge-upload zijn, gaat het *niet* om een XMLHttpRequest. 
			// Een "application/json" header zal dan een ongewenste download veroorzaken. 
			// (Of als de JSONView extensie is geinstalleerd, krijg je json als versmurft als html)
			header('Content-Type: text/html');
		} else {
			header('Content-Type: application/json');			
		}				
		echo json_encode($this->data);
	}
	
	/**
	 * Dit Component kan niet binnen een ander component getoond worden.
	 * 
	 * @return bool
	 */
	function isWrapable() {
		return false;
	}  
}  
?>