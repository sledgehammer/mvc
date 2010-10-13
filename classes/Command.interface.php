<?php
/**
 * Implementatie voor een Command structuur. 
 * DesignPatterns: Command, Action
 *
 * @package MVC
 */

interface Command {

	/**
	 * De generateContent() bouwt een view object op en returnt deze.
	 * 
	 * @return Component
	 */
	function generateContent();
}
?>
