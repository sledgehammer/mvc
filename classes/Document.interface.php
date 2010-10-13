<?php
/**
 * 
 *
 * @package MVC
 */

interface Document extends Component {
	
	/**
	 * @return bool
	 */
	function isDocument();

	/**
	 * @return array
	 */
	function getHeaders();
/*
 *
 * De basis voor het weergeven en opslaan van geparste documenten, geschikt voor zowel html, csv, xml, afbeeldingen, etc
 *
 * DesignPattern: Singleton/Globals, MVC/Model 2
 *
	public
		$charset, 
		$headers = array(), // Array met HTTP header informatie
		$component;  // Het component dat gerenderd zal worden

	/**
	 * @param string $charset De charset van het document (wordt o.a. gebruikt in implode_xml_parameters()) zie http://www.php.net/htmlentities voor beschikbare charsets
	 * /
	function __construct($charset = 'UTF-8') {
		$this->charset = $charset;
	}
	/** 
	 * De HTTP headers versturen.
	 * De inhoud van het document weergeven
	 *
	 * @return void
	 * /
	function render() {
		send_headers($this->headers);
		render($this->component);
	}

	/**
	 * Het document opslaan.
	 * Als het bestand als *.php bestand wordt opgeslagen, dan worden de phptags geescaped en de headers toegevoegd.
	 *
	 * @return void
	 * /
	function save_as($filename, $format = NULL) {
		if ($format === NULL) {
			$format = file_extension($filename);
		}
		$buffer = component_to_string($this->component);
		switch($format) {

			case 'php':
				$buffer_prefix = "<?php\n/**\n * Generated on: ".date('Y-m-d H:i:s')." * /\n";
				foreach($this->headers AS $header) {
					$buffer_prefix .= "\theader('".addslashes($header)."');\n";
				}
				$buffer_prefix .= '?>';
				$buffer = $buffer_prefix.str_replace('<?','<?php echo "<?"; ?>', $buffer);
				break;
		}
		file_put_contents($filename, $buffer);
	}
	
	/**
	 * Dit Component kan niet binnen een ander component getoond worden. 
	 * @return bool
	 * /
	function isWrapable() {
		return false;
	}*/
}
?>
