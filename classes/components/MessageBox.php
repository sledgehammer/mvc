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
		$message;

	/**
	 * @param string $icon  Bestandsnaam van een Icoon uit de "images/dialog/" map
	 * @param string $title  Ttitel van het bericht
	 * @param string $message  Inhoud van het bericht
	 */
	function __construct($icon, $title, $message) { // [void]
		if (in_array($icon, array('warning', 'error', 'done'))) {
			$icon = WEBROOT.'mvc/images/MessageBox/'.$icon.'.png';
		}
		$this->icon = $icon;
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
		$template = new Template('MessageBox.html', array(
			'icon' => $this->icon,
			'title' => $this->title,
			'message' => $this->message,
		));
		$template->render();
	}
}
?>
