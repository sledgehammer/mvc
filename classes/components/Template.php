<?php
/**
 * Een component voor het weergeven van php-templates.
 * De templates zijn standaard php. er wordt geen gebruik gemaakt van een tempate engine zoals bv Smarty.
 *
 * @package MVC
 */

class Template extends Object implements Component {

	static $templateFolders = array();

	public
		$template, 
		$variables; 

	/**
	 * @param string Bestandsnaam van de template (exclusief thema map)
	 * @param array $variables Variabelen die in de template worden gezet. Als je array('naam' => value) meegeeft kun in de template {$naam} gebruiken
	 */
	function __construct($template, $variables = array()) {
		$this->template = $template;
		$this->variables = $variables;
	}

	/**
	 * De template parsen en weergeven
	 */
	function render() {
		foreach (self::$templateFolders as $folder) {
			if (file_exists($folder.'/'.$this->template)) {
				extract($this->variables);
				return include($folder.'/'.$this->template);
			}
		}
		warning('Template: "'.$this->template.'" not found', array('folders' => self::$templateFolders));
	}

	static function addTemplateFolder($path) {
		array_unshift(self::$templateFolders, $path); // schuif het nieuwe thema vooraan
	}
}
?>
