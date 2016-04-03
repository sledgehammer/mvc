<?php

// Global functions inside the Sledgehammer namespace

namespace Sledgehammer;

use Exception;
use Sledgehammer\Mvc\Component;
use Sledgehammer\Core\Framework;

/**
 * Geeft de uitvoer van een component als string.
 * (Uitvoer zoals emails en header() worden niet afgevangen).
 *
 * @param Component $component
 *
 * @return string
 */
function component_to_string($component)
{
    if (is_valid_component($component) === false) {
        return false;
    }
    ob_start();
    try {
        $component->render();
    } catch (Exception $e) {
        $output = ob_get_clean();
        report_exception($e);

        return $output;
    }

    return ob_get_clean();
}

/**
 * Check if $component is compatible with the Component interface, otherwise report notices.
 *
 * @param Component $component
 *
 * @return bool
 */
function is_valid_component(&$component = '__UNDEFINED__')
{
    if (is_component($component)) {
        return true;
    }
    if (is_object($component)) {
        notice(get_class($component).' is not an component', get_class($component).' doesn\'t implement a render() method');
    } elseif ($component == '__UNDEFINED__') {
        notice('Variable is undefined');
    } else {
        notice('Invalid datatype: "'.gettype($component).'", expecting a Component object');
    }

    return false;
}

/**
 * Zet een array om naar xml/html parameters; array('x' => 'y') wordt ' x="y"'.
 *
 * @param array  $parameterArray
 * @param string $charset        De charset van de parameters (voor htmlentities). Standaard wordt de charset van het actieve document gebruikt.
 *
 * @return string
 */
function implode_xml_parameters($parameterArray, $charset = null)
{
    $xml = '';
    if ($charset === null) {
        $charset = Framework::$charset;
    }
    foreach ($parameterArray as $key => $value) {
        $xml .= ' '.$key.'="'.htmlentities($value, ENT_COMPAT, $charset).'"';
    }

    return $xml;
}

/**
 * Zet een string met parameters om naar een array.
 * ' x="y"' wordt  array('x' => 'y').
 *
 * @param string $parameterString
 *
 * @return array
 */
function explode_xml_parameters($parameterString)
{
    /* De reguliere expressies manier kan niet omgaan met values die geen quotes hebben e.d..
      if (preg_match_all('/(?P<attr>[a-z]*)=[\"\'](?P<value>[a-zA-Z0-9\/._-]*)[\"\']/', $parameterString, $match)) {
      foreach ($match['attr'] as $index => $key) {
      $parameters[$key] = $match['value'][$index];
      }
      }
      // */
    $parameters = array();
    $state = 'NAME';
    // Parse the string via a state-machine
    while ($parameterString) {
        switch ($state) {

            case 'NAME': // Zoek de attribuut naam.(de tekst voor de '=')
                $equalsPos = strpos($parameterString, '=');
                if (!$equalsPos) { // er zijn geen attributen meer.
                    break 2; // stop met tokenizing
                }
                $value = trim(substr($parameterString, 0, $equalsPos));
                $value = preg_replace('/.*[ \t]/', '', $value); // als er een spatie of tab in de naam staat, haal deze (en alles ervoor) weg
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
 * Merge the results from the $component->getHeaders().
 * The headers from the $component overwrites the values in $headers.
 *
 * @param array           $headers
 * @param array|Component $component A component or header array
 *
 * @return array
 */
function merge_headers($headers, $component)
{
    if (is_string(array_value($headers, 'css'))) {
        $headers['css'] = array($headers['css']);
    }

    if (is_array($component)) { // Is er een header array meegegeven i.p.v. een component?
        $appendHeaders = $component;
    } elseif (method_exists($component, 'getHeaders')) {
        $appendHeaders = $component->getHeaders();
    } else {
        return $headers; // Er zijn geen headers om te mergen.
    }
    foreach ($appendHeaders as $category => $values) {
        switch ($category) {

            case 'title':
                $headers['title'] = $values;
                break;

            case 'css':
            case 'javascript':
                if (is_string($values)) {
                    $values = array($values);
                }
                if (empty($headers[$category])) {
                    $headers[$category] = $values;
                } else {
                    $headers[$category] = array_merge($headers[$category], $values);
                }
                break;

            default:
                if (!is_array($values)) {
                    notice('Invalid "'.$category.'" header: values not an array, but a '.gettype($values), array('values' => $values));
                } elseif (empty($headers[$category])) {
                    $headers[$category] = $values;
                } else {
                    $headers[$category] = array_merge($headers[$category], $values);
                }
                break;
        }
    }

    return $headers;
}

/**
 * Stel de $parameters['class'] in of voegt de $class toe aan de $parameters['class'].
 *
 * @param string $class
 * @param array  $parameters
 */
function append_class_to_parameters($class, &$parameters)
{
    if (isset($parameters['class'])) {
        $parameters['class'] .= ' '.$class;
    } else {
        $parameters['class'] = $class;
    }
}

/**
 * ID and NAME must begin with a letter ([A-Za-z]) and may be followed by any number of letters, digits ([0-9]), hyphens ("-"), underscores ("_"), colons (":"), and periods (".").
 */
function tidy_id($cdata)
{
    $cdata = trim($cdata);
    $cdata = str_replace(array('[', ']'), '.', $cdata);

    return $cdata;
}
