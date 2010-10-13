<?php
/**
 * Superklasse van Website objecten, een combinatie van een Builder en een Handler
 * DesignPatterns: Builder, Command, Chain of Responsibility & Singleton
 *
 * @package MVC
 */

abstract class Website extends VirtualFolder {

	/**
	 * De Website inititaliseren
	 */
	function __construct() {
		parent::__construct();
		$this->publicMethods = array_diff($this->publicMethods, array('init', 'render', 'statusbar', 'wrapComponent', 'onDatabaseFailure', 'initDocument', 'initLanguage', 'isWrapable')); // Een aantal functies *niet* public maken
		$this->initLanguage();
		// $this->sessionStart(); // Initialiseer de sessie		
	}

	/**
	 * Genereerd de inhoud van de pagina (in een Component)
	 * @return Component
	 */
	function generateContent() {
		if ($GLOBALS['database_failure']) { // Zijn er geen database problemen? 
			return $this->onDatabaseFailure();
		}
		return parent::generateContent();
	}

	/*function generateDocument() {
		$component = $this->generateContent();
		if (!defined('MICROTIME_EXECUTE')) {
			define('MICROTIME_EXECUTE', microtime(true));
		}
		if (!is_component($component)) {
			warning(get_class($this).'->generateContent() should return a Component, instead of '.syntax_highlight($component));
		}
		else {
			$isWrapable = true;
			if (method_exists($component, 'isWrapable')) {
				$isWrapable =  $component->isWrapable();
			}
			if ($isWrapable) {
				$component = $this->wrapComponent($component);
			} else {
				$component->render();
				return;
			}
		}
		$document = new HTMLDocument();
		$document->component = $component;
		return $document;
	}*/

	function handleRequest() {
		$content = $this->generateContent();
		$isDocument = false;
		if (method_exists($content, 'isDocument')) {
			$isDocument =  $content->isDocument();
		}
		if ($isDocument) {
			$document = $content;
		} else {
			$document = $this->generateDocument($content);
		}
		$headers = $document->getHeaders();
		send_headers($headers['http']);
		$document->render();
	}

	/**
	 *
	 * @return Template
	 */
	protected function generateDocument($content) {
		$document = new HTMLDocument();
		$document->content = $content;
		return $document;
	}

	protected function wrapComponent($component) {
		return $component;
	}

	/**
	 * Als er een database verbinding is mislukt zal deze functie wordt aangeroepen om de request af te handelen.
	 *
	 * @return Component
	 */
	protected function onDatabaseFailure() {
		$this->document->headers[] = $_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error';
		return new MessageBox ('error.png', 'Er is een fout opgetreden', 'Er kon geen verbinding gemaakt worden met de database.');
	}

	/**
	 * Het Document object aanmaken en initialiseren.
	 *
	 * @return HTMLDocument
	 * /
	function getDocument() {
		return $this->document;
	}

	protected function initDocument() {
		$this->document = new HTMLDocument;
		
		return $this->document;
	}
	*/

	/**
	 * Stel de Locale in zodat getallen en datums op de juiste manier worden weergegeven
	 *
	 * @param NULL|string $language engelse benaming van de taal die moet worden ingesteld.
	 * @return void
	 */
	protected function initLanguage($language = NULL) {
		if ($language === NULL) {
				return;
		}
		switch ($language) {

			case 'dutch':
				$locales = array('nl_NL.utf8', 'nl_NL.UTF-8', 'dutch');
				break;

			default:
				warning('Invalid language: "'.$language.'"');
				return;
		}
		if (!setlocale(LC_ALL, $locales)) { 	
			exec('locale -a', $available_locales);
			notice('Setting locale to "'.implode('", "', $locales).'" has failed', 'Available locales: "'.implode('", "', $available_locales).'"');
		} elseif (setlocale(LC_ALL, 0) == 'C') {
			notice('setlocale() failed. (Cygwin issue)');
		}
	}

	/**
	 * Geeft informatie over: parsetijden, geheugenverbruik, queries, etc
	 */
	function statusbar() {
		statusbar();
	}

	/**
	 * De sessie starten, biedt de mogenlijkheid voor sessies in de database 
	 */
	protected function sessionStart() { // [void]
		if (headers_sent($bestand, $regel)) {
			notice('Sessie kon niet gestart worden. Er was uitvoer in '.$bestand.' op regel '.$regel);
		} else {
			if (isset($_SESSION)) { // Is de sessie al gestart?
				return; 
			}
			// Voorkom PHPSESSID in de broncode bij zoekmachines
			if (isset($_SERVER['HTTP_USER_AGENT'])) { // Is de browser meegegeven?
				/*
				$bots = file(dirname(__FILE__).'/../settings/http_user_agent_session_blacklist.csv', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES);
				foreach($bots as $bot) {
					if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false) { // Gaat het om een zoekmachine bot?
						ini_set('url_rewriter.tags', ''); // Geen PHPSESSID in de html code stoppen.
						break;
					}
				}*/
			} else { // Er is geen user_agent/browser opgegeven, waarschijnlijk dan geen browser
				ini_set('url_rewriter.tags', ''); // Geen PHPSESSID in de html code stoppen
			}
			session_start();
		}
	}
}
?>
