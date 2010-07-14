<?php
/**
 * Interface van Componenten, Zodra een object aan deze interface voldoet kan hij gebruikt worden als viewport
 *
 * @package MVC
 */

interface Component {

	/**
	 * De uitvoer van het component weergeven.
	 * Hierin staan typisch "echo" statements en $Smarty->display($template)
	 *
	 * @return void
	 */
	function render();
}
?>
