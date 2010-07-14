<?php
/**
 * Dit website object cached alle uitvoer van een website, tenzij de filename / folder een timeout van 0 hebben. 
 * We gaan er vanuit dat de <div class="statusbar"> net boven de </body></html> staat.
 * Om het de uitvoer zonder statusbalken weer te gegeven moet je "?debug=0&nocache=1" toevoegen aan de url.
 * ?nocache=cookie zorgt ervoor dat er zolang het cookie bestaat er niet gecached wordt. Met ?nocache=nocookie reset je de waarde in het cookie
 *
 * @todo uitbreiden met een speciale url die de cache bestanden verwijderd.
 * @package MVC
 */

class WebsiteCache extends Website {

	public
		$file_timeouts = array(),
		$folder_timeouts = array();

	private
		$Website,
		$cache_folder,
		$default_timeout,
		$timeout;

	function __construct($Website, $default_timeout = 3600) {
		$this->Website = $Website;
		$this->default_timeout = $default_timeout;
		$this->cache_folder = 'tmp/website_cache/'.URL::info('host');
		if (Website::$show_statusbar) {
			$this->cache_folder .= '_debug';
		}
		$this->depth_increment = 0;
	}

	function render() {
		$extracted_path = URL::extract_path();
		$folders = $extracted_path['folders'];
		$filename = $extracted_path['filename'];
		$path = implode('/' ,$folders);
		if ($path == '') {
			$path = '/'.$filename;
		} else {
			$path = '/'.$path.'/'.$filename;
		}
		$this->timeout = @$this->file_timeouts[$path];
		if ($this->timeout === NULL) {
			for($i = count($folders) - 1; $i >= 0; $i--) {
				$folder_path = implode('/' ,$folders);
				$this->timeout = @$this->folder_timeouts[$folder_path];
				if ($this->timeout !== NULL) {
					break;
				}
				unset($folders[$i]);
			}
		}
		if ($this->timeout === NULL) {
			$this->timeout = $this->default_timeout; 
		}
		if ($this->timeout == 0 || @$_GET['nocache'] || @$_COOKIE['nocache'] || @$_GET[SledgeHammer::get_setting('debug_override_variable')]) { // Deze pagina niet in de cache bewaren?
			if (isset($_GET['nocache'])) {
				switch ($_GET['nocache']) {

					case 'cookie':
						setcookie('nocache', true);
						break;

					case 'nocookie':
						setcookie('nocache', false, 0);
						break;
				}
			}
			$this->Website->render();
		} else { // Deze pagina in de cache bewaren
			$filename = PATH.$this->cache_folder.$path.'.php';
			$timestamp = @filemtime($filename); // Opvragen wanneer deze pagina gecached is
			$cached = true;
			if($timestamp === false || (time() - $timestamp) > $this->timeout) { // 
				$this->Website->init();
				$this->Website->execute();
				mkdirs(dirname($filename));
				$GLOBALS['Document']->save_as($filename);
				$cached = false;
			}
			include($filename); // cache file parsen
			if (Website::$show_statusbar) {
				// Extensie achterhalen
				preg_match('/[^.]+$/', $filename, $extention);
				$extention = $extention[0];
				if ($extention == 'php') {
					preg_match('/[^.]+(.php)$/', $filename, $extention);
					$extention = substr($extention[0], 0, -4);
				}
				// Alleen html bestanden uitbreiden met extra debug informatie
				switch ($extention) {

					case 'html':
					case 'xhtml':
					case 'htm':
						if ($cached) {
							echo ' --><div class="statusbarcache"><strong>Cachefile</strong> ';
							parent::statusbar();
							echo '</div></div></body></html>';
						} else {
							echo ' --></div></body></html>';
						}
						break;
				}
			}
		}
	}

	/**
	 * De statusbalk die in de gecachte pagina terecht komt
	 */
	function statusbar() {
		if (!(@$_GET[SledgeHammer::get_setting('debug_override_variable')] || @$_GET['nocache'] || @$_COOKIE['nocache'] || $this->timeout == 0)) {
			echo 'Cache expires: <strong>'.date('Y-m-d H:i:s', time() + $this->timeout).'</strong>. Timeout: <strong>'.$this->timeout.'</strong>sec. ';
			parent::statusbar();
			echo '<!-- ';
		} else {
			parent::statusbar();
		}
	}

	/**
	 * De cache map legen
	 */
	private function flushCache() {
		$file_count = rmdirs(PATH.'tmp/cache/');
		$title = 'Cache opschonen';
		if (isset( $_ENV['HOSTNAME'])) {
			$title .= ' - Server: '.$_ENV['HOSTNAME'];
		}
		$GLOBALS['Viewport'] = new MessageBox('ok.gif', $title, $file_count.' bestanden verwijderd');
	}

}
?>
