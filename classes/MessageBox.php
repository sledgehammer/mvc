<?php
/**
 * Een bericht weergeven, Wordt veel gebruikt voor (gebruiker zichtbare) foutmeldingen.
 * In tegenstelling tot Visual Basic, C++, enz heeft deze MessageBox geen "ok" of terug knop.
 *
 * @package MVC
 */
namespace SledgeHammer;
class MessageBox extends Object implements View {

	public
		$icon,
		$title,
		$message;

	/**
	 * @param string $icon  Bestandsnaam van een Icoon
	 * @param string $title  Ttitel van het bericht
	 * @param string $message  Inhoud van het bericht
	 */
	function __construct($icon, $title, $message) { // [void]
		if (in_array($icon, array('warning', 'error', 'done'))) {
			$icon = WEBROOT.'mvc/icons/MessageBox/'.$icon.'.png';
		}
		$this->icon = $icon;
		$this->title = $title;
		$this->message = $message;
	}

	function getHeaders() {
		return array(
			'title' => $this->title
		);
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
