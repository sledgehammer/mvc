<?php
/**
 * Superklasse van Website objecten, een combinatie van een Builder en een Handler
 * DesignPatterns: Builder, Command, Chain of Responsibility & Singleton
 *
 * @package MVC
 */
namespace SledgeHammer;
abstract class Website extends VirtualFolder {

	/**
	 * De Website inititaliseren
	 */
	function __construct() {
		parent::__construct();
		$this->publicMethods = array_diff($this->publicMethods, array('handleRequest', 'generateDocument', 'statusbar',  'initLanguage', 'isWrapable')); // Een aantal functies *niet* public maken
	}

	/**
	 * Aan de hand van de Request een Response versturen
	 *
	 * @return void
	 */
	function handleRequest() {
		$document = $this->generateDocument();
		if (!defined('SledgeHammer\MICROTIME_EXECUTE')) {
			define('SledgeHammer\MICROTIME_EXECUTE', microtime(true));
		}
		$headers = $document->getHeaders();
		send_headers($headers['http']);
		$document->render();
	}

	/**
	 *
	 * @return Document
	 */
	function generateDocument() {
		if ($GLOBALS['database_failure']) { // Zijn er geen database problemen?
			$content = $this->onDatabaseFailure();
		} else {
			$content = $this->generateContent();
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
	 * @return View
	 */
	abstract protected function wrapContent($content);

	/**
	 * Als er een database verbinding is mislukt zal deze functie wordt aangeroepen om de request af te handelen.
	 *
	 * @return View
	 */
	protected function onDatabaseFailure() {
		$html = export_view(new MessageBox('error.png', 'Er is een fout opgetreden', 'Er kon geen verbinding gemaakt worden met de database.'));
		return new HTML($html, array(
			'http' => array('Status' => '500 Internal Server Error')
		));
	}


	/**
	 * Geeft informatie over: parsetijden, geheugenverbruik, queries, etc
	 */
	function statusbar() {
		statusbar();
	}
}
?>
