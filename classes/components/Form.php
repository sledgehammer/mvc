<?php
/**
 * Form
 */
namespace Sledgehammer;
/**
 * Generate and import a Form
 * @package MVC
 */
class Form extends Object implements View, Import {

	/**
	 * @var array
	 */
	public $fields;

	/**
	 * @var array
	 */
	private $attributes = array(
		'method' => 'post'
	);

	/**
	 * Constructor
	 * @param array $options
	 */
	function __construct($options = array()) {
		// Set attributes and properties
		foreach ($options as $option => $value) {
			if (property_exists($this, $option)) {
				$this->$option = $value;
			} else {
				$this->attributes[$option] = $value;
			}
		}
		if (empty($this->attributes['action'])) {
			$this->attributes['action'] = URL::getCurrentURL();
		}
	}

	function initial($values) {
		foreach ($values as $field => $value) {
			$this->fields[$field]->initial($value);
		}
	}

	function import(&$error, $request = null) {
		if ($request === null) {
			if (strtolower($this->attributes['method']) === 'post') {
				$request = $_POST;
			} elseif (strtolower($this->attributes['method']) === 'get') {
				$request = $_GET;
			} else {
				notice('Invalid import method');
				$request = $_REQUEST;
			}
		}
		if (count($request) == 0) {
			$error = false;
			return null;
		}
		$data = array();
		foreach ($this->fields as $key => $field) {
			$data[] = $field->import($fieldError, $request);
			if ($fieldError) {
				$error[$fieldError] = $fieldError;
			}
		}
		if (count($error)) {
			return null;
		}
		return $data;
	}

	function render() {
		echo HTML::element('form', $this->attributes, true), "\n";
		$this->renderContents();
		echo '</form>';
	}

	function renderContents() {
		if (array_value($this->attributes, 'class') === 'form-horizontal') {
			$renderControlGroups = true;
		} else {
			$renderControlGroups = false;
		}

		foreach ($this->fields as $label => $field) {
			if ($renderControlGroups) {
				echo '<div class="control-group">';
				if (is_int($label) === false) {
					echo '<label class="control-label">', HTML::escape($label), '</label>';
				}
				echo '<div class="controls">';
				render($field);
				echo "</div></div>\n";
			} else {
				if (is_int($label) === false) {
					echo '<label>', HTML::escape($label), '</label>';
				}
				render($field);
				echo "\n";
			}
		}
	}

}

?>
