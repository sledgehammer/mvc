<?php
/**
 * Global function of the MVC module.
 *
 * @package MVC
 */
// Functions that are available everywhere (global namespace)

namespace {

    /**
     * render($view) is an alias to $view->render()
     * but render($view) generates a notice when the $view issn't a View compatible object instead of an fatal error
     */
    function render($view) {
        if (Sledgehammer\is_valid_view($view)) {
            $view->render();
        }
    }

    /**
     * Check if the $view parameter is compatible with the View interface via ducktyping.
     *
     * @param View $view
     * @return bool
     */
    function is_view(&$view = '__UNDEFINED__') {
        return (is_object($view) && method_exists($view, 'render'));
    }

    /**
     * Genereer een <script src=""> tag, mits deze al een keer gegenereerd is.
     * @param string $src
     * @param string $identifier
     * @return void
     */
    function javascript_once($src, $identifier = null) {
        static $included = array();

        if ($identifier === null) {
            $identifier = $src;
        }
        if (isset($included[$identifier])) {
            return;
        }
        $included[$identifier] = true;
        echo '<script type="text/javascript" src="'.$src.'"></script>'."\n";
    }

}
// Global functions inside the Sledgehammer namespace

namespace Sledgehammer {

    /**
     * Geeft de uitvoer van een component als string.
     * (Uitvoer zoals emails en header() worden niet afgevangen)
     *
     * @param View $view
     * @return string
     */
    function view_to_string($view) {
        if (is_valid_view($view) === false) {
            return false;
        }
        ob_start();
        try {
            $view->render();
        } catch (\Exception $e) {
            $output = ob_get_clean();
            report_exception($e);
            return $output;
        }
        return ob_get_clean();
    }

    /**
     * Check if $component is compatible with the View interface, otherwise report notices
     *
     * @param View $view
     * @return bool
     */
    function is_valid_view(&$view = '__UNDEFINED__') {
        if (is_view($view)) {
            return true;
        }
        if (is_object($view)) {
            notice('Invalid $view, class "'.get_class($view).'" must implement a render() method');
        } elseif ($view == '__UNDEFINED__') {
            notice('Variable is undefined');
        } else {
            notice('Invalid datatype: "'.gettype($view).'", expecting a View object');
        }
        return false;
    }

    /**
     * Convert an array to xml/html attributes; array('x' => 'y') becomes' x="y"'
     *
     * @param array $attributes
     * @param string [$charset]  The charset for converting special characters in the value. Defaults to framework setting (UTF-8)
     * @return string
     */
    function implode_xml_attributes($attributes, $charset = null) {
        $xml = '';
        if ($charset === null) {
            $charset = Framework::$charset;
        }
        foreach ($attributes as $key => $value) {
            if (is_int($key)) {
                $xml .= ' '.$value;
            } elseif ($value === true) {
                $xml .= ' '.$key;
            } else {
                $xml .= ' '.$key.'="'.htmlentities($value, ENT_COMPAT, $charset).'"';
            }
        }
        return $xml;
    }

    /**
     * @deprecated
     */
    function implode_xml_parameters($attributes, $charset = null) {
        deprecated('Renamed implode_xml_parameters() to implode_xml_attributes()');
        return implode_xml_attributes($attributes, $charset);
    }

    /**
     * Convert a string with xml/html attributes into an array.
     * ' x="y"' wordt  array('x' => 'y')
     *
     * @param string $xml
     * @return array
     */
    function explode_xml_attributes($xml, $charset = null) {
        if ($charset === null) {
            $charset = Framework::$charset;
        }
        $attributes = [];
        $state = 'ATTRIBUTE';
        $attribute = false;
        if ($xml[0] !== '<') {
            $xml = '<tag '.$xml.' />'; // fool the html tokenizer
        }
        $tokenizer = new HtmlTokenizer($xml);
        foreach ($tokenizer as $i => $token) {
            if (is_string($token)) {
                $tokenType = $token;
                $tokenValue = $token;
            } else {
                $tokenType = $token[0];
                $tokenValue = $token[1];
                if ($tokenType === 'T_WHITESPACE') {
                    continue;
                }
                if ($tokenType === 'T_CLOSE') {
                    break;
                }
            }
            switch ($state) {
                case 'ATTRIBUTE':
                    if ($tokenType === 'T_ATTRIBUTE') {
                        $attribute = $token[1];
                        $state = 'ATTRIBUTE_FOUND';
                    }
                    break;

                case 'ATTRIBUTE_FOUND':
                    if ($tokenType === 'T_ASSIGNMENT') {
                        $state = 'VALUE';
                    } elseif ($tokenType === 'T_ATTRIBUTE') {
                        $attributes[$attribute] = true; // previous attribute was a boolean attribute
                        $attribute = $token[1];
                    }
                    break;

                case 'VALUE':
                    if ($tokenType === 'T_VALUE') {
                        $attributes[$attribute] = html_entity_decode($token[1], ENT_NOQUOTES, $charset);
                        $state = 'ATTRIBUTE';
                    } elseif ($tokenType !== 'T_DELIMITER') {
                        $attributes[$attribute] = ''; // recover from an error
                        $state = 'ATTRIBUTE';
                    }
                    break;
                default:
                    error('Invalid state: '.$state);
            }
        }
        if ($state === 'ATTRIBUTE_FOUND') { // last attribute was a boolean attribute?
            $attributes[$attribute] = true;
        }
        return $attributes;
    }

    /**
     * @deprecated
     */
    function explode_xml_parameters($xml) {
        deprecated('Renamed explode_xml_parameters() to explode_xml_attributes()');
        return explode_xml_attributes($xml);
    }

    /**
     * Het resultaat van 2 View->getHeaders() samenvoegen.
     * De waardes in $header1 worden aangevuld en overschreven door de waardes in header2
     *
     * @param array $headers
     * @param array|View $view Een component of een header array
     * @return array
     */
    function merge_headers($headers, $view) {
        if (is_string(array_value($headers, 'css'))) {
            $headers['css'] = array($headers['css']);
        }

        if (is_array($view)) { // Is er een header array meegegeven i.p.v. een View?
            $appendHeaders = $view;
        } elseif (method_exists($view, 'getHeaders')) {
            $appendHeaders = $view->getHeaders();
        } else {
            return $headers; // Er zijn geen headers om te mergen.
        }
        foreach ($appendHeaders as $category => $values) {
            switch ($category) {

                case 'title':
                    $headers['title'] = $values;
                    break;

                case 'base':
                    $headers['base'] = $values;
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

}
?>