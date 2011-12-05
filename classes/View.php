<?php
/**
 * Interface for the views
 *
 * @package MVC
 */
namespace SledgeHammer;
interface View {

	/**
	 * Render the view to the client (echo statements)
	 *
	 * @return void
	 */
	function render();

	/**
	 * (Optional method)
	 * An array with view dependencies, that should be sent in the HTTP header or inside <head> tag.
	 *
	 * @return array
	 */
	//function getHeaders();
}
?>
