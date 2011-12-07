<?php
/**
 * XML View
 *
 * @package MVC
 */
namespace SledgeHammer;

class XML extends Object implements Document {

	/**
	 * @var SimpleXMLElement|DOMDocument
	 */
	private $xml;

	function __construct($xml) {
		$this->xml = $xml;
	}

	function render() {
		if ($this->xml instanceof \SimpleXMLElement) {
//			echo $this->xml->asXML();
			$dom = dom_import_simplexml($this->xml)->ownerDocument;
			$dom->formatOutput = true;
			echo $dom->saveXML();
		} else {
			echo $this->xml;
		}
	}

	function isDocument() {
		return true;
	}

	function getHeaders() {
		return array(
			'http' => array(
				'Content-Type' => 'text/xml',
			)
		);
	}

	/**
	 *
	 * @param array $data
	 * @return \SimpleXMLElement
	 */
	static function build($data, $charset = null) {
		if (count($data) != 1) {
			throw new \Exception('The array should contain only 1 (root)element');
		}
		if ($charset === null) {
			$charset = $GLOBALS['charset'];
		}
		reset($data);
		$root = key($data);
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="'.$charset.'"?><'.$root.' />');
		self::addNodes($xml, current($data), $root);
		return $xml;
	}

	/**
	 * @param \SimpleXMLElement $xml
	 * @param array $data
	 */
	static function addNodes($xml, $data, $node, $detectEncoding = false) {
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				if (is_int($key)) {
					$key = $node;
				}
				$tree = $xml->addChild($key);
				self::addNodes($tree, $value, $key);
			} else {
				$xml->$key = $value;
			}
		}
	}

}

?>
