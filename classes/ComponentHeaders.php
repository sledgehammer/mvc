<?php
/**
 * Add Headers to any Component.
 * Makes it possible to add headers from a Controller without adding a headers to all Component classes.
 *
 *
 * @package MVC
 */
namespace SledgeHammer;
class ComponentHeaders extends Object implements Component {

	/**
	 * @var Component
	 */
	public $component;
	/**
	 * @var array
	 */
	public $headers;
	/**
	 * @var bool
	 */
	public $overrideHeaders;
	
	/**
	 *
	 * @param Component $component
	 * @param array $headers
	 * @param bool $overrideHeaders Bij false zullen de headers van het component leidend zijn.
	 */
	function  __construct($component, $headers, $overrideHeaders = false) {
		$this->component = $component;
		$this->headers = $headers;
	}
	
	function getHeaders() {
		if ($this->overrideHeaders == false) {
			return merge_headers($this->headers, $this->component); //  standaard merge
		}
		// De headers van dit object zijn leidend.
		if (method_exists($this->component, 'getHeaders')) {
			return merge_headers($this->component->getHeaders(), $this->headers);
		}
		return $this->headers; // Het component had geen headers.
	}

	function render() {
		$this->component->render();
	}

	function isDocument() {
		if (method_exists($this->component, 'isDocument'))
		{
			return $this->component->isDocument();
		}
		return false;
	}
}
?>
