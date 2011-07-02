<?php
/**
 * De gegevens van een Object filteren en via een platte API geschikbaar stellen.
 *
 * @package MVC
 */
namespace SledgeHammer;
class ObjectView extends AbstractView {

	protected $_object;

	/**
	 * De brondata instellen
	 *
	 * @param object $object
	 * @return void
	 */
	function setData($object) {
		if ($this->_object === NULL) { // Was er nog geen Object ingesteld?
			// De filters uitbreiden aan de hand van de eigenschappen
			$properties = get_object_vars($object);
			foreach ($properties as $property => $value) {
				if (!isset($this->_filters[$property])) { // Is er nog geen filter ingesteld?
					$this->_filters[$property] = parent::detectFilter($property, gettype($value));
				}				
			}
			if (count($this->_fields) == 0) { // Zijn er geen fields gedefineerd?
				$this->setFields(array_keys($properties)); // Stel dan de eigenschappen van het object in als fields.
			}
		}
		$this->_object = $object;
	}
	
	function __clone() {
		$this->_object = clone $this->_object;
	}

	protected function detectFilter($name) {
		notice('No filter configured for property: "'.$name.'"');
		return parent::detectFilter($name);
	}

	/**
	 * Een eigenschap van het Object opvragen
	 *
	 * @param string $property
	 * @return mixed
	 */
	function _getRawElement($property) {
		return $this->_object->$property;
	}
}
?>
