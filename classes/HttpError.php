<?php
/**
 * Geeft een HTTP foutmelding naar de gebruiker
 * Overschrijft de <title> en headers
 * 
 * @todo Overige HTTP errors toevoegen.
 *
 * @package MVC
 */
namespace SledgeHammer;
class HttpError extends Object implements Component {

	/**
	 * Een HTTP Error Code, bijvoorbeeld 404
	 * @var int
	 */
	private $errorCode;
	private $options;

	/**
	 * Maak een HTTP-error aan
	 *
	 * @param int $statusCode  HTTP Foutcode van de fout 404,403 enz
	 * @param int $options  Array met optionele instellingen: array(
	 *   'notice' => Geeft deze notice na het renderen.
	 *   'warning' => Geeft deze warning na het renderen. 
	 * 
	 */
	function __construct($errorCode, $options = array()) {
		$this->errorCode = $errorCode;
		$this->options = $options;
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
		$messageBox = new MessageBox($error['icon'], $error['title'], $error['message']."<br />\n".value($_SERVER['SERVER_SIGNATURE']));
		$messageBox->render();
		foreach ($this->options as $option => $value) {
			switch ((string) $option) {
				
				case 'notice':
				case 'warning':
					$function = $option;
					if (is_array($value)) {
						call_user_func_array($function, $value);
					} else {
						call_user_func($function, $value);
					}
					break;
				
				default:
					notice('Unknown option: "'.$option.'"', array('value' => $value));
					break;
			}
		}
	}

	private function getError() {

		switch ($this->errorCode) {

			case 400:
				return array(
					'header' => 'Bad Request',
					'icon' => 'error',
					'title' => 'Bad Request',
					'message' => 'Server begreep de aanvraag niet'
				);

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
					'message' => (substr(URL::getCurrentURL()->path, -1) == '/') ? 'U mag de inhoud van deze map niet bekijken' : 'U mag deze pagina niet bekijken',
				);
	
			case 404:	
				return array(
					'header' => 'Not Found',
					'icon'=> 'warning',
					'title' => 'Bestand niet gevonden',
					'message' => 'De opgegeven URL "'.URL::getCurrentURL().'" kon niet worden gevonden.',
				);

			case 500:
				return array(
					'header' => 'Internal Server Error',
					'icon'=> 'error',
					'title' => 'Interne serverfout',
					'message' => 'Er is een interne fout opgetreden, excuses voor het ongemak.',
				);

			case 501:
				return array(
					'header' => 'Not Implemented',
					'icon' => 'error',
					'title' => 'Not Implemented',
					'message' => 'Dit wordt niet door de server ondersteund'
				);

			default:
				throw new \Exception('HTTP errorCode '.$this->errorCode.' is not (yet) supported.');

		}
	}
}
?>
