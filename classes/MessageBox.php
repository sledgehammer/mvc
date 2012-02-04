<?php
/**
 * Een bericht weergeven, Wordt veel gebruikt voor (gebruiker zichtbare) foutmeldingen.
 * In tegenstelling tot Visual Basic, C++, enz heeft deze MessageBox geen "ok" of terug knop.
 *
 * @package MVC
 */

namespace SledgeHammer;

class MessageBox extends Object implements View {

	/**
	 * @var string
	 */
	private $icon;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @param string $icon  warning|error|done or  url of the image
	 * @param string $title
	 * @param string $message
	 */
	function __construct($icon, $title, $message) {
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
		$template = new Template('MessageBox.php', array(
			'icon' => $this->icon,
			'title' => $this->title,
			'message' => $this->message,
		));
		$template->render();
	}

}

?>
