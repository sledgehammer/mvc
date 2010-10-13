<?php
/**
 * Globale functies van de Webcore module
 *
 * @package MVC
 */


/**
 * Geeft de uitvoer van een component als string.
 * (Uitvoer zoals emails en header() worden niet afgevangen)
 *
 * @return string
 */
function component_to_string($component) {
	if (is_component($component, true)) {
		ob_start();
		$component->render();
		return ob_get_clean();
	}
}

/**
 * render($component) is hetzelde als een $component->render(), 
 * Maar render($component) geeft *geen* fatal error als er geen render() methode in het object zit.
 */
function render($component) {
	if(is_component($component, true)) {
		$component->render();
	} 
}

/**
 * Controleer of de $variable een component compatible object is.
 *
 */
function is_component(&$variable = '__UNDEFINED__', $show_notice = false) {
	if (is_object($variable) && method_exists($variable, 'render')) {
		return true;
	} elseif ($show_notice) {
		if (is_object($variable)) {
			notice('Invalid $component, class "'.get_class($variable).'" must implement a render() method');
		} elseif ($variable == '__UNDEFINED__') {
			notice('Variable is undefined');
		} else {
			notice('Invalid datatype: "'.gettype($variable).'", expecting a Component object');
		}
	}
	return false;
}

/**
 * Functie waarmee je vanuit smarty kunt opvragen of de viewport is ingesteld.
 */
function viewport_exists($viewport) { // [bool]
	$viewports = explode(' ', $viewport);
	$pointer = &$GLOBALS['Viewports'];
	foreach($viewports as $viewport) {
		if (!isset($pointer[$viewport])) {
			return false;
		}
		$pointer = &$pointer[$viewport];
	}
	return true;
}

/**
 * Zet een array om naar xml/html parameters; array('x' => 'y') wordt ' x="y"'
 * 
 * @param array $parameters
 * @param string $charset  De charset van de parameters (voor htmlentities). Standaard wordt de charset van het actieve document gebruikt.
 * @return string
 */

function implode_xml_parameters($parameterArray, $charset = null) {
	$xml = '';
	if ($charset === null) {
		$charset = $GLOBALS['charset'];
	}
	foreach($parameterArray as $key => $value) {
		$xml .= ' '.$key.'="'.htmlentities($value, ENT_COMPAT, $charset).'"';
	}
	return $xml;
}
function parse_xml_parameters($parameters) {
	deprecated('Use implode_xml_parameters() instead');
	return implode_xml_parameters($parameters);
}

/**
 * Zet een string met parameters om naar een array.
 *' x="y"' wordt  array('x' => 'y') 
 * 
 * @param string $tag
 * @return array
 */
function explode_xml_parameters($parameterString) {
	/* De reguliere expressies manier kan niet omgaan met values die geen quotes hebben e.d..
	if (preg_match_all('/(?P<attr>[a-z]*)=[\"\'](?P<value>[a-zA-Z0-9\/._-]*)[\"\']/', $parameterString, $match)) {
		foreach ($match['attr'] as $index => $key) {
			$parameters[$key] = $match['value'][$index];		
		} 
	}
	//*/
	$parameters = array();
	$state = 'NAME';
	// Parse de string via een state-machine
	while ($parameterString) {
		switch ($state) {
			
			case 'NAME': // Zoek de attribuut naam.(de tekst voor de '=') 
				$equalsPos = strpos($parameterString, '=');
				if (!$equalsPos) { // er zijn geen attributen meer.
					break 2; // stop met tokenizing
				}
				$value = trim(substr($parameterString, 0, $equalsPos));
				$value = preg_replace('/.*[ \t]/','', $value); // als er een spatie of tab in de naam staat, haal deze (en alles ervoor) weg
				$attributeName = $value; // attribuutnaam is bekend.

				$parameterString = ltrim(substr($parameterString, $equalsPos + 1)); // De parameterstring inkorten.
				$delimiter = substr($parameterString, 0, 1);
				if ($delimiter != '"' && $delimiter != "'") { // Staan er geen quotes om de value?
					$delimiter = ' \t>';
					$escape = '';
				} else {
					$parameterString = substr($parameterString, 1); // de quote erafhalen.
					$escape = '\\'.$delimiter;
				}
				$state = 'VALUE';
				break;
				
			case 'VALUE':			
				if (preg_match('/^([^'.$delimiter.']*)['.$delimiter.']/', $parameterString, $match)) {
					$parameters[$attributeName] = $match[1]; // De waarde is bekend.
					$parameterString = substr($parameterString, strlen($match[0]));
					$state = 'NAME';
					break;
				} else { // geen delimiter? dan is het de laatste value,
					$parameters[$attributeName] = $parameterString; // De waarde is bekend.
					break 2;
				}

			default:
				error('Invalid state');
		}		
	}
	return $parameters;
}

/**
 * Het resultaat van 2 Component->getHeaders() samenvoegen.
 * De waardes in $header1 worden aangevuld en overschreven door de waardes in header2
 *
 * @param array $headers
 * @param Component $component
 * @return array
 */
function merge_headers($headers, $component) {
	if (method_exists($component, 'getHeaders')) {
		$appendHeaders = $component->getHeaders();
		foreach ($appendHeaders as $category => $values) {
			if (empty ($headers[$category])) { // Staat deze category nog niet in de headers?
				$headers[$category] = $values;
				continue;
			}
			if ($category == 'title') {
				$headers['title'] = $values;
			} else {
				$headers[$category] = array_merge($headers[$category], $values);
			}
		}
	}
	return $headers;
}

/**
 * Stel de $parameters['class'] in of voegt de $class toe aan de $parameters['class'] 
 * 
 * @param string $class
 * @param array $parameters 
 * @return void
 */
function append_class_to_parameters($class, &$parameters) {
	if (isset($parameters['class'])) {
		$parameters['class'] .= ' '.$class;
	} else {
		$parameters['class'] = $class;
	}
}

/** 
 * ID and NAME must begin with a letter ([A-Za-z]) and may be followed by any number of letters, digits ([0-9]), hyphens ("-"), underscores ("_"), colons (":"), and periods (".").
 */
function tidy_id($cdata) {
	$cdata = trim($cdata);
	$cdata = str_replace(array('[', ']'), '.', $cdata);
	return $cdata;
}

/**
 * Sends the contents of the file (if necessary) including headers
 * 
 * @param string $filename Filename including path.
 * @return bool
 */
function mirror_file($filename) {
	deprecated('Use render_file()');
	return render_file($filename);
}

/**
 * Genereer een <script src=""> tag, mits deze al een keer gegenereerd is.
 * @param string $src
 * @param string $identifier
 * @return void
 */
function javascript_once($src, $identifier = null) {
	if ($identifier === null) {
		$identifier = $src;
	}
	if (isset($GLOBALS['included_javascript'][$identifier])) {
		return;
	}
	$GLOBALS['included_javascript'][$identifier] = true;
	echo '<script type="text/javascript" src="'.$src.'"></script>'."\n";
}

/**
 * Genereer een warning() voor de EerrorHandler en verstuur de foutmelding in Json formaat naar de browser.
 * Deze dient dan via javascript de error te tonen. Met bv: showError(result.errorMsg)
 * 
 * @param string|Exception $errorMsg  De foutmelding string
 * @param string $messageVarname  De naam van de variabele die gebruikt wordt in de json response voor de foutmelding. (ext_improved.js gaat uit van "errorMsg")
 * @return Json
 */
function jsonError($errorMsg, $messageVarname = 'errorMsg') {
	if ($errorMsg instanceof Exception) {
		ErrorHandler::handle_exception($errorMsg);
		$errorMsg = $errorMsg->getMessage();
	} else {
		warning('JsonError: '. $errorMsg);
	}
	return new Json(array(
		'success' => false,
		$messageVarname => $errorMsg
	));
}

/**
 * Short for "new Json(array('success' => true))"
 * @param array $data (optioneel) Gegevens die naast de success worden meegestuurd.
 */
function jsonSuccess($data = array()) {
	return new Json(array_merge(
		$data,
		array('success' => true)
	));
}
?>
