<?php
/**
 * Intialize the MVC module
 * 
 * @package MVC
 */
namespace SledgeHammer;

$modules = Framework::getModules();
foreach ($modules as $module) {
	$templateFolder = $module['path'].'templates/';
	if (file_exists($templateFolder)) {
		Template::addTemplateFolder($templateFolder);
	}
}
if (defined('SledgeHammer\WEBROOT')) {
	// Import the WEBROOT & WEBPATH into the global scope
	define('WEBROOT', \SledgeHammer\WEBROOT);
	define('WEBPATH', \SledgeHammer\WEBPATH);
}
?>
