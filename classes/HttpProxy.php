<?php
/**
 * HttpProxy
 */
namespace Sledgehammer;
/**
 * Load the contents and http-headers of the url and use them as a remote FileDocument
 *
 * @package MVC
 */
class HttpProxy extends Object implements View {

	private $headers = array();
	private $contents;

	function __construct($url) {
		if (substr($url, 0, 7) !== 'http://' && substr($url, 0, 8) !== 'https://') {
			throw new \Exception('Not a valid url');
		}
		$options = array(
			CURLOPT_CUSTOMREQUEST => $_SERVER['REQUEST_METHOD'],
			CURLOPT_FAILONERROR => false,
			CURLOPT_HEADER => true,
		);
		if (DebugR::isEnabled()) {
			$options[CURLOPT_HTTPHEADER] = array('DebugR: '.$_SERVER['HTTP_DEBUGR']);
		}
		// @todo Forward $_POST data

		$response = Curl::get($url, $options);
		$this->headers['http'] = $response->getHeaders();
		$this->contents = $response->getBody();
		$this->headers['http']['Status'] = $response->http_code.' '.Framework::$statusCodes[$response->http_code];
	}

	public function render() {
		echo $this->contents;
	}

	function getHeaders() {
		return $this->headers;
	}

	function isDocument() {
		return true;
	}

}

?>
