<?php
/**
 * Een bericht weergeven, Wordt veel gebruikt voor (gebruiker zichtbare) foutmeldingen.
 * In tegenstelling tot Visual Basic, C++, enz heeft deze MessageBox geen "ok" of terug knop.
 *
 * @package MVC
 */

class MessageBox extends Object implements Component {

	public
		$icon,
		$title,
		$message,
		$template;

	/**
	 * @param string $icon_filename  Bestandsnaam van een Icoon uit de "images/dialog/" map
	 * @param string $title  Ttitel van het bericht
	 * @param string $message  Inhoud van het bericht
	 * @param string $template  Template bestand dat wordt weergegeven
	 */
	function __construct($icon_filename, $title, $message, $template = 'MessageBox.html') { // [void]
		if (!preg_match('/\.[^.]{2,4}$/', $icon_filename)) { // Ongeldige bestandsnaam? (geen  extentie)
			notice('Invalid filename icon_filename: "'.$icon_filename.'"');
		}
		$this->icon = WEBROOT.'mvc/images/MessageBox/'.$icon_filename;
		$this->title = $title;
		$this->message = $message;
		$this->template = $template;
	}

	/**
	 * De MessageBox weergeven
	 *
	 * @return void
	 */
	function render() {
		$template = new Template($this->template, array(
			'icon' => $this->icon,
			'title' => $this->title,
			'message' => $this->message,
		));
		$template->render();
	}
}
?>
