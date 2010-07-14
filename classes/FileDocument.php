<?php
/**
 * Een bestand op het bestandsysteem naar de client sturen.
 * De MVC variant van de render_file() functie.
 *
 * @package MVC
 */

class FileDocument extends Object implements Component {

	public
		$headers = array();

	private
		$filename,
		$error = false,
		$notModified = false,
		$etag;

	/**
	 * @param array $options  array('etag'=> bool)
	 */
	function __construct($filename, $options = array('etag' => false)) {
		$this->filename = $filename;
		$this->etag = array_value($options, 'etag');
		if (!file_exists($filename)) {
			if (basename($filename) == 'index.html') {
				$this->headers[] = $_SERVER['SERVER_PROTOCOL'].' 403 Forbidden';
				$this->error = 403;
			} else {
				$this->headers[] = $_SERVER['SERVER_PROTOCOL'].' 404 Not Found';
				$this->error = 404;
			}
			return;
		}
		$last_modified = filemtime($filename);
		if ($last_modified === false) {
			$this->headers[] = $_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error';
			$this->error = 500;
			return;
		}
		if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER)) {
			$if_modified_since = strtotime(preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']));
			if ($if_modified_since >= $last_modified) { // Is the Cached version the most recent?
				$this->headers[] = $_SERVER['SERVER_PROTOCOL'].' 304 Not Modified';
				$this->notModified = true;
				return;
			}
		}
		if ($this->etag) {
			$etag = md5_file($filename);
			if (array_value($_SERVER, 'HTTP_IF_NONE_MATCH') === $etag) {
				$this->headers[] = $_SERVER['SERVER_PROTOCOL'].' 304 Not Modified';
				$this->notModified = true;
				return;
			}
			$this->headers[] = 'ETag: '.md5_file($filename);
		}
		$this->notModified = false;
		if (is_dir($filename)) {
			$this->headers[] = $_SERVER['SERVER_PROTOCOL'].' 403 Forbidden';
			$this->error = 403;
			return;
		}
		$this->headers[] = 'Content-Type: '.mimetype($filename);
		$this->headers[] = 'Last-Modified: '.gmdate('r', $last_modified);
		$filesize = filesize($filename);
		if ($filesize === false) {
			$this->headers[] = $_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error';
			$this->error = 500;
			return;
		}
		$this->headers[] = 'Content-Length: '.$filesize; // @todo Detecteer bestanden groter dan 2GiB, deze geven fouten.
	}

	function render() {
		if ($this->error) {
			if ($this->error == 404) { // Bij een 404 error een notice geven. De 500's geven al een notice. 
				notice('HTTP[404] File "'.URL::uri().'" not found');
			} elseif ($this->error == 403) { // De 403 error wel loggen maar niet mailen.
				error_log('HTTP[403] Directory listing for "'.URL::uri().'" not allowed');
			}
			$httpError = new HttpError($this->error);
			$component = $httpError->execute();
			$component->render();
			return;	
		}
		send_headers($this->headers);
		if ($this->notModified) {
			return;
		}
		readfile($this->filename);
	}

	/**
	 * Dit component kan niet binnen een ander component getoond worden.
	 *  
	 * @return bool
	 */	
	function isWrapable() {
		if ($this->error) {
			return true;
		}
		return false;
	}
}
?>
