<?php
/**
 *
 * @package MVC
 */
namespace SledgeHammer;
interface Controller {

	/**
	 * Build The up an view
	 *
	 * @return View
	 */
	function generateContent();
}
?>
