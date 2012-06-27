<?php
/**
 * Website
 */
namespace Sledgehammer;
/**
 * Superclass for the Website classes.
 * DesignPatterns: FrontController, Command, Chain of Responsibility
 *
 * @package MVC
 */
abstract class Website extends VirtualFolder {

	function __construct() {
		parent::__construct();
		$this->publicMethods = array_diff($this->publicMethods, array('handleRequest', 'generateDocument', 'statusbar',  'initLanguage', 'isWrapable')); // Een aantal functies *niet* public maken
	}

	/**
	 * Send a Response based on the Request
	 *
	 * @return void
	 */
	function handleRequest() {
		$document = $this->generateDocument();
		if (!defined('Sledgehammer\GENERATED')) {
			define('Sledgehammer\GENERATED', microtime(true));
		}
		$headers = $document->getHeaders();
		send_headers($headers['http']);
		$document->render();
	}

	/**
	 * Generate a Document for this request
	 *
	 * @return Document
	 */
	function generateDocument() {
		try {
			$content = $this->generateContent();
		} catch (\Exception $exception) {
			$content = new HttpError(500, array('exception' => $exception));
		}
		$isDocument = false;
		if (method_exists($content, 'isDocument')) {
			$isDocument =  $content->isDocument();
		}
		if ($isDocument) {
			return $content;
		}
		$document = new HTMLDocument();
		$document->content = $this->wrapContent($content);
		return $document;
	}

	/**
	 * Imbed the view inside your Layout View
	 *
	 * @return View
	 */
	abstract protected function wrapContent($content);


	/**
	 * Render debugging information.
	 * Allow subclasses to render custom statusbar info.
	 */
	function statusbar() {
		statusbar();
	}
}
?>
