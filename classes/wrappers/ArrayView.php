<?php
/**
 * De gegevens van een array via een platte API beschikbaar stellen.
 *
 * @package MVC
 */
namespace Sledgehammer;
class ArrayView extends AbstractView {

	private
		$_array;
	
	function setData($array) {
		$this->_array = $array;
		if (count($this->_fields) == 0) { // Zijn er geen fields gedefineerd?
			$this->setFields(array_keys($array)); // Stel dan de keys van het (eerste) array in als fields.
		}
	}

	function _getRawElement($key) {
		return $this->_array[$key];
	}
}
?>
