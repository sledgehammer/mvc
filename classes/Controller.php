<?php
/**
 * Controller
 * @package MVC
 */
namespace Sledgehammer;
/**
 * Interface for Controllers, the C in MVC.
 */
interface Controller {

	/**
	 * Build and return a view object
	 *
	 * @return View
	 */
	function generateContent();

}
?>
