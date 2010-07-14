<?php
/**
 * Webcore module initializeren
 * 
 * @param string $debug_override_variable  De naam van de $_GET of $_COOKIE variabele die de instellingen overschrijven. Als deze op `false` wordt gezet kan de debug niet overschreven worden. (Heeft invloed op `error_handler_html`, `display_errors`, `statusbar`)
 * @package MVC
 */

$modules = SledgeHammer::getModules();
foreach ($modules as $module) {
	$templateFolder = $module['path'].'templates/';
	if (file_exists($templateFolder)) {
		Template::addTemplateFolder($templateFolder);
	}
}
?>
