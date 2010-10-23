<?php
/**
 * Geeft een HTTP foutmelding naar de gebruiker
 * Overschrijft de <title> en headers
 * 
 * @todo Overige HTTP errors toevoegen.
 *
 * @package MVC
 */

class HttpError extends Object implements Component {

	/**
	 * Een HTTP Error Code, bijvoorbeeld 404
	 * @var int
	 */
	private $errorCode;

	/**
	 * Maak een HTTP-error aan
	 *
	 * @param int $statusCode foutcode van de fout 404,403 enz
	 */
	function __construct($errorCode) {
		$this->errorCode = $errorCode;
	}

	function getHeaders() {
		$error = $this->getError();
		return array(
			'title' => $this->errorCode.' - '.$error['title'],
			'http' => array('Status' => $this->errorCode.' '.$error['header']),
		);
	}

	/**
	 * Genereer een MessageBox met de foutmelding
	 *
	 * @return void
	 */
	function render() {
		$error = $this->getError();
		//if ($GLOBALS['ErrorHandler']->html && !headers_sent()) { // Worden er foutrapporten ge-echo-t worden?
		//	header($_SERVER['SERVER_PROTOCOL'].' '.$this->errorCode.' '.$error['header']);
		//}
		$messageBox = new MessageBox($error['icon'], $error['title'], $error['message']."<br />\n".value($_SERVER['SERVER_SIGNATURE']));
		$messageBox->render();
	}

	private function getError() {

		switch ($this->errorCode) {

			case 401:
				return array(
					'header' => 'Unauthorized',
					'icon'=> 'warning',
					'title' => 'Niet geauthoriseerd',
					'message' => 'U heeft onvoldoende rechten om deze pagina te bekijken.',
				);

			case 403:
				return array(
					'header' => 'Forbidden',
					'icon'=> 'warning',
					'title' => 'Verboden toegang',
					'message' => (substr(URL::info('path'), -1) == '/') ? 'U mag de inhoud van deze map niet bekijken' : 'U mag deze pagina niet bekijken',
				);
	
			case 404:
				return array(
					'header' => 'Not Found',
					'icon'=> 'warning',
					'title' => 'Bestand niet gevonden',
					'message' => 'De opgegeven URL "'.URL::uri().'" kon niet worden gevonden.',
				);

			case 500:
				return array(
					'header' => 'Internal Server Error',
					'icon'=> 'error',
					'title' => 'Interne serverfout',
					'message' => 'Er is een interne fout opgetreden, excuses voor het ongemak.',
				);
				
			default:
				throw new Exception('HTTP errorCode '.$this->errorCode.' is not (yet) supported.');

		}
	}
}
?>