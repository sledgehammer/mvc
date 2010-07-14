<?php
/**
 * Een component voor het weergeven van de google analytics tracker js code
 *
 * @package MVC
 */

class GoogleAnalytics extends Object implements Component {

	public
		$code; 

	/**
	 *  
	 * @param string $code  "UA-xxxxxxx-x"
	 */
	function __construct($code) {
		//@todo code valideren
		$this->code = $code;
	}

	function render() {
		$srcPrefix = (value($_SERVER['HTTPS']) == 'on') ? 'https://ssl' : 'http://www';
		javascript_once($srcPrefix.'.google-analytics.com/ga.js'); 
		echo '<script type="text/javascript">'."\n";
		echo '	var pageTracker = _gat._getTracker("'.$this->code.'");'."\n";
		echo '	pageTracker._initData();'."\n";
		echo '	pageTracker._trackPageview();'."\n";
		echo '</script>';
	}
}
?>
