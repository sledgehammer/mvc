<?php
/**
 * De gegevens van een Object of Array filteren en via platte Array of Object API beschikbaar stellen.
 *
 * @package MVC
 */
namespace SledgeHammer;
abstract class AbstractView extends Object implements \ArrayAccess, \Iterator {

	protected
		$_fields; // Iterator

	public
		$_defaultFilter = 'xss',
		$_filters,
		$_keyFilter, // Filter die gebruik wordt voor in  Iterator::key() functie
		$_complexTypes;
		
	private
		$_subfield_prefix,
		$_subfields; // @var Iterator
		

	/**
	 * @param array $complexType array waarmee elementen van subobjecten benaderd kunnen worden. array('stockitem_' => array('source' => 'StockItem', 'view' => new StockItemView))
	 * @param array $filters
	 */
	function __construct($complexTypes = array(), $filters = array(), $fields = array()) {
		if (is_array($complexTypes) == false) {
			warning('Unexpected datatype "'.gettype($complexTypes).'" for parameter $ComplexTypes, expecting an array');
		} else { // Valideer de elementen in het array
			foreach ($complexTypes as $name => $config) {
				if (preg_match('/^[0-9]+/', $name)) {
					warning('ComplexType prefix can\'t start with a number', '$test->12_test is invalid php');
				} elseif (is_array($config) == false) {
					warning('Unexpected type: "'.gettype($config).'" for complextype "'.$name.'", expecting array("prefix_" => array("source"=> $element, "view" => $viewObject))');
				} elseif (empty($config['source'])) {
					warning('ComplexType : "'.$name.'" has no source element', $config);
				} elseif (empty($config['view'])) {
					warning('ComplexType : "'.$name.'" has no view element', $config);
				}
			}
		}
		$this->_complexTypes = $complexTypes;
		$this->_filters = $filters;
		$this->setFields($fields);
	}

	/**
	 * De gegevens instellen waar de View zijn gegevens uit haald.
	 *
	 * @param mixed $data
	 * @return void
	 */
	abstract function setData($data);

	/**
	 *
	 * @param array $fields
	 * @return void
	 */
	function setFields($fields) {
		$this->_fields = new \ArrayIterator($fields);
	}
	/**
	 * Vraag een eigenschap op uit de ingestelde $data.
	 *
	 * @param $identifier
	 * @return mixed
	 */
	abstract protected function _getRawElement($identifier);

	/**
	 * Als er geen filter is ingesteld in $this->_filters[$element] dan wordt deze functie aangeroepen om een filter te selecteren.
	 *
	 * @param string $element De naam/identifier van het element.
	 * @param string|null $type Het datatype van het element.
	 * @return string|false Naam van de filter
	 */
	protected function detectFilter($element) {
		return $this->_defaultFilter;
	}


	/**
	 * Een eigenschap opvragen.
	 * By default zal er een string geretourneerd worden waar de html karakters al zijn ge-escape-t.
	 *
	 * @param string $identifier Naam van het element
	 * @return mixed
	 */
	private function _getElement($identifier, $useFilter = true) {
		$filter = null;
		if (array_key_exists($identifier, $this->_filters)) {
			$filter = $this->_filters[$identifier];
		} else { // Als er geen filter is kan het gaan om een ComplexType
			// Controleren of de eigenschap een onderdeel is van een ComplexType
			foreach ($this->_complexTypes as $prefix => $complexType) {
				$prefixLength = strlen($prefix);
				if (substr($identifier, 0, $prefixLength) == $prefix) { // Heeft de opgevraagde eigenschap een bekende prefix?
					$complexType['view']->setData($this->_getElement($complexType['source'], false));
					$subElement = substr($identifier, $prefixLength);
					return $complexType['view'][$subElement];
				}
			}
			// Het is geen ComplexType
		}
		if ($useFilter && $filter == 'deny') { // De 'deny' filter is een uitzondering omdat in deze situatie de property niet opgrvraagd dient te worden.
			throw new \Exception('Access to element "'.$identifier.'" is denied');
		}
		// Controleer of deze View class een functie heeft "get_$identifier()"
		$method = 'get_'.$identifier;
		if (method_exists($this, $method)) {
			$value = $this->$method();
		} else {
			$value = $this->_getRawElement($identifier);
		}
		// De waarde eventueel filteren
		if ($useFilter) {
			if ($filter === null) { // Is de filter nog niet bepaald (geen complextype, maar ook geen filter bekend.)
				$filter = $this->detectFilter($identifier, gettype($value)); // De filter detecteren
				if ($filter == 'deny') { // Is de gedetecteerde filter de 'deny' filter?
					throw new \Exception('Access to element "'.$identifier.'" is denied');
				}
			}
			if ($filter !== false) {
				$method = $filter.'_filter';
				if (!method_exists($this, $method)) {
					throw new \Exception('Filter-function "'.$filter.'_filter($value)" doesn\'t exist in '.get_class($this));
				}
				$value = $this->$method($value, $identifier); // De waarde filteren
			}
		}
		return $value;
	}

	/**
	 * HTML karakters escapen en newlines omzetten naar <br />
	 * Beschermt tegen XSS
	 *
	 * @param string unescaped utf8 string
	 * @return string escaped utf8 string
	 */
	protected function toHtml_filter($value) {
		return str_replace("\n" ,"<br />\n", htmlentities($value, ENT_COMPAT, 'UTF-8'));
	}

	/**
	 * Deze filter kijkt naar het datatype en geeft een html-safe waarde terug.
	 * In tegenstelling tot de toHtml filter worden booleans e.d. niet omgezet naar een string.
	 *
	 * @param mixed $value
	 * @return mixed xss-safe waarde
	 */
	protected function xss_filter($value) {
		switch (gettype($value)) {

			case 'string':
				return $this->toHtml_filter($value);

			case 'NULL':
			case 'boolean':
			case 'integer':
			case 'double':
				return $value; // Deze types kunnen geen xss tags bevatten.

			case 'object':
				if (method_exists($value, '__toString')) { // Kan het object omgezet worden naar een string? 
					return $this->toHtml_filter((string) $value); // Omzet naar string en deze string escapen. 
				}
				notice('Objects without __toString() implementation are not allowed');
				return null;
				
			default:
			case 'resource':
			case 'array': 
				notice('Unacceptable type: "'.gettype($value).'" for xss_filter()');
				return null;
		}
	}
	
	/**
	 * Zorgt ervoor dat de View van het complexType wordt opgevraagd.
	 * Daarnaast wordt de ComplexType ook opgenomen in de iterator.
	 * 
	 * @param mixed $value
	 * @param string $element
	 * @return View
	 */
	protected function complexType_filter($value, $element) {
		foreach ($this->_complexTypes as $complexType) {
			if ($complexType['source'] == $element) {
				$view = $complexType['view'];
				$view->setData($value);
				return $view;
			}
		}
		notice('No ComplexType found with "'.$element.'" as source');
	}

	/**
	 * Magic getter en setter functies zodat je dat de eigenschappen zowel als object->eigenschap kunt opvragen
	 */

	function __get($property) {
		return $this->_getElement($property);
	}

	function __set($property, $value) {
		throw new \Exception('View objects are readonly');
	}

	/**
	 * ArrayAccess wrapper functies die er voor zorgen dat de eigenschappen ook als array['element'] kunt opgevragen
	 */

	function offsetGet($property) {
		return $this->_getElement($property);
	}
	function offsetSet($property, $value) {
		throw new \Exception('View objects are readonly');
	}
	function offsetUnset($property) {
		throw new \Exception('View objects are readonly');
	}
	function offsetExists($property) {
		throw new \Exception('offsetExists() not (yet) supported');
	}

	/**
	 * Iterator functions waardoor je de elementen via een iterator_to_array() kunt omzetten naar een array.
	 * Hierdoor kun je via convert_iterators_to_arrays()
	 */
	function rewind() {
		return $this->_fields->rewind(); 
	}
	
	function current() {
		if ($this->_subfield_prefix === null) {
			return $this->_getElement($this->_fields->current());
		} else {
			return $this->_subfields->current();
		}		
	}
	
	function next() {
		if ($this->_subfield_prefix === null) {
			$this->_fields->next();
			$field = $this->_fields->current();
			if (isset($this->_filters[$field]) && $this->_filters[$field] == 'complexType') {
				foreach ($this->_complexTypes as $prefix => $complexType) {
					if ($complexType['source'] == $field) {
						$this->_subfields = $this->_getElement($field);
						if ($this->_subfields instanceof Iterator) {
							$this->_subfields->rewind();
							$this->_subfield_prefix = $prefix;
						} 
					}
				}
			}
		} else {
			$this->_subfields->next();
			if ($this->_subfields->valid()) {
				return;
			} 
			$this->_subfield_prefix = null;
			$this->_fields->next();
		}
	}
	
	function key() {
		if ($this->_subfield_prefix !== null) {
			return $this->_subfield_prefix.$this->_subfields->key();
		} else {
			$value = $this->_fields->current();
		}		
		$filter = ($this->_keyFilter === null) ? $this->_defaultFilter : $this->_keyFilter;
		if ($filter !== false) {				
			$method = $filter.'_filter';
			if (!method_exists($this, $method)) {
				throw new \Exception('Filter-function "'.$filter.'_filter($value)" doesn\'t exist in '.get_class($this));
			}
			$value = $this->$method($value); // De waarde filteren
		}
		return $value;
	}
	
	function valid() {
		return $this->_fields->valid();
	}
}
?>
