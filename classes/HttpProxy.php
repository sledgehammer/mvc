<?php
/**
 * HttpProxy, load the contents and http-headers of the url and use them as a MVC Component.
 *
 * @package MVC
 */
namespace SledgeHammer;

class HttpProxy extends Object implements Component {

	private $error = false;
	private $headers = array();
	private $contents;

	function __construct($url) {
		$this->contents = file_get_contents($url);
		if ($this->contents !== false) {
			// Parse the headers that came with the file_get_contents() call.
			foreach ($http_response_header as $header) {
				if (preg_match('/^HTTP\/1.[01](.+)$/', $header, $match)) {
					$this->headers['Status'] = ltrim($match[1]);
					continue;
				}
				$pos = strpos($header, ':');
				$this->headers[substr($header, 0, $pos)] = ltrim(substr($header, $pos + 1));
			}
		} else {
			if (isset($http_response_header)) {
				foreach ($http_response_header as $header) {
					if (preg_match('/^HTTP\/1.[01](.+)$/', $header, $match)) {
						$status = intval($match[1]);
						if ($status >= 400) { // Skip 30x redirects
							$this->error = new HttpError($status);
						}
					}
				}
			}
			if ($this->error === false) {
				$this->error = new HttpError(500);
			}
		}
	}

	public function render() {
		if ($this->error) {
			$this->error->render();
			return;
		}
		echo $this->contents;
	}

	function getHeaders() {
		if ($this->error) {
			return $this->error->getHeaders();
		}
		return array(
			'http' => $this->headers
		);
	}

	function isDocument() {
		return ($this->error === false);
	}

}

?>