<?php
/**
 * Renders the data as Json, or Jsonp
 *
 * @package MVC
 */
namespace SledgeHammer;

class Json extends Object implements Document {

	/**
	 * @var mixed $data  utf8 encoded data.
	 */
	private $data;

	/**
	 * @param mixed  $data      The data to be sent as json
	 * @param string $charset   The encoding used in $data, use null for autodetection. Assume UTF-8 by default
	 */
	function __construct($data, $charset = 'UTF-8') {
		if (strtoupper($charset) !== 'UTF-8') {
			$this->data = $this->convertToUTF8($data, $charset);
		} else {
			$this->data = $data;
		}
	}

	/**
	 * Change Content-Type to "application/json"
	 */
	function getHeaders() {
		if (count($_FILES) == 0) {
			return array(
				'http' => array(
					'Content-Type' => 'application/json',
				)
			);
		} else {
			// Als er bestanden ge-upload zijn, gaat het *niet* om een XMLHttpRequest, maar waarschijnlijk om een upload naar een hidden iframe via javascript.
			// Een "application/json" header zal dan een ongewenste download veroorzaken.
			// (Of als de JSONView extensie is geinstalleerd, wordt de json versmurft als html)
			return array(
				'http' => array(
					'Content-Type' => 'plain/text',
				)
			);
		}
	}

	/**
	 * Render the $data as json
	 */
	function render() {
		$json = json_encode($this->data);
		$error = json_last_error();
		if ($error !== JSON_ERROR_NONE) {
			if ($GLOBALS['ErrorHandler']->html) {
				header('Content-Type: text/html; charset=UTF-8');
			}
			notice('[Json error] '.self::translateJsonError($error));
		}
		echo $json;
	}

	/**
	 * Render a standalone document
	 *
	 * @return bool
	 */
	function isDocument() {
		return true;
	}

	/**
	 *
	 * @param mixed       $data     The non UTF-8 encoded data
	 * @param string|null $charset  The from_encoding, Use null for autodetection
	 * @return mixed  UTF8 encoded data
	 */
	private function convertToUTF8($data, $charset) {
		if (is_string($data)) {
			return mb_convert_encoding($data, 'UTF-8', $charset);
		}
		if (is_object($data)) {
			$data = get_object_vars($data);
		}
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = $this->convertToUTF8($value, $charset);
			}
			return $data;
		}
		// Is $data a integer, float, etc
		return $data;
	}

	static function translateJsonError($error) {
		switch ($error) {
			case JSON_ERROR_NONE: return false;
			case JSON_ERROR_DEPTH: return 'The maximum stack depth has been exceeded';
			case JSON_ERROR_STATE_MISMATCH: return'Invalid or malformed JSON';
			case JSON_ERROR_CTRL_CHAR: return'Control character error, possibly incorrectly encoded';
			case JSON_ERROR_SYNTAX: return 'Syntax error';
			case JSON_ERROR_UTF8: return'Malformed UTF-8 characters, possibly incorrectly encoded';
			default:
				return 'Unknown error['.$error.']';
		}
	}

}

?>