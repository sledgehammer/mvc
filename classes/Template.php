<?php
/**
 * Een component voor het weergeven van php-templates.
 * De templates zijn standaard php. er wordt geen gebruik gemaakt van een tempate engine zoals bv Smarty.
 *
 * @package MVC
 */
namespace SledgeHammer;
class Template extends Object implements Component {

	/**
	 *
	 * @var array
	 */
	static $templateFolders = array();

	/**
	 * Bestandsnaam van de template (exclusief thema map)
	 * @var string
	 */
	public $template;
	/**
	 * Variabelen die in de template worden gezet. Als je array('naam' => value) meegeeft kun in de template {$naam} gebruiken
	 * @var array  
	 */
	public $variables;
	/**
	 * De variable die gebruikt wordt voor de getHeaders()
	 * @var array  
	 */
	public $headers;

	/**
	 *
	 * @param string $template
	 * @param array $variables
	 * @param array $headers
	 */
	function __construct($template, $variables = array(), $headers = array()) {
		$this->template = $template;
		$this->variables = $variables;
		$this->headers = $headers;
	}

	/**
	 * Vraag de ingestelde headers op van deze template en eventuele subcomponenten
	 * @return array
	 */
	function getHeaders() {
		$headers = $this->headers;
		$components = $this->getComponents($this->variables);
		foreach ($components as $component) {
			$headers = merge_headers($headers, $component);
		}
		return $headers;
	}

	/**
	 * De template parsen en weergeven
	 * 
	 * @return void
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

	private function getComponents($array) {
		$components = array();
		foreach ($array as $element) {
			if (is_component($element)) {
				$components[] = $element;
			} elseif (is_array($element)) {
				$nestedComponents = $this->getComponents($element);
				foreach ($nestedComponents as $component) {
					$components[] = $component;
				}
			}
		}
		return $components;

	}
}
?>
