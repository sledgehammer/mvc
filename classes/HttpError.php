<?php
/**
 * Geeft een HTTP foutmelding naar de gebruiker
 * Overschrijft de <title> en headers
 * 
 * @todo Overige HTTP errors toevoegen.
 *
 * @package MVC
 */

class HttpError extends Object implements Command {

	private
		$http_status_code;

	/**
	 * Maak een HTTP-error aan
	 *
	 * @param int $http_status_code foutcode van de fout 404,403 enz
	 */
	function __construct($http_status_code) {
		$this->http_status_code = $http_status_code;
	}

	/**
	 * Stel de Document->headers in en genereer en MessageBox met de foutmelding
	 *
	 * @return void
	 */
	function execute() {
		$httpStatusses = array(
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error'
		);

		if (!isset($httpStatusses[$this->http_status_code])) {
			warning('http_status_code '.$this->http_status_code.' is not (yet) supported.');
			return false;
		}
		$header = $_SERVER['SERVER_PROTOCOL'].' '.$this->http_status_code.' '.$httpStatusses[$this->http_status_code];
		if ($GLOBALS['ErrorHandler']->html && !headers_sent()) { // Worden er foutrapporten ge-echo-t worden?
			header($header); // Verstuur van de header direct, zodat deze ook in debugmode verstuurd wordt 
		}
		$errors = array(
			401 => array(
				'icon'=> 'warning.png',
				'title' => 'Niet geauthoriseerd',
				'message' => 'U heeft onvoldoende rechten om deze pagina te bekijken.',
			),
			403 => array(
				'icon'=> 'warning.png',
				'title' => 'Verboden toegang',
				'message' => (substr(URL::info('path'), -1) == '/') ? 'U mag de inhoud van deze map niet bekijken' : 'U mag deze pagina niet bekijken',
			),
			404 => array(
				'icon'=> 'warning.png',
				'title' => 'Bestand niet gevonden',
				'message' => 'De opgegeven URL "'.URL::uri().'" kon niet worden gevonden.',
			),
			500 => array(
				'icon'=> 'error.png',
				'title' => 'Interne serverfout',
				'message' => 'Er is een interne fout opgetreden, excuses voor het ongemak.',
			),
		);
		$error = $errors[$this->http_status_code];
		getDocument()->headers['http_status_code'] = $header;
		getDocument()->title = $this->http_status_code.' - '.$error['title'];
		return new MessageBox($error['icon'], $error['title'], $error['message']."<br />\n".value($_SERVER['SERVER_SIGNATURE']));
	}
}
?>
