<?php
/**
 * Superclasse van de Virtuele mappen.
 *  Door VirtualFolder creer je eenvoudig virtuele mappen en virtuele bestanden.
 *  Hierdoor heb je vrijheid in de paden die je gebruikt om de pagina's aan te duiden. I.p.v. "page.php?id=17" maak je "pages/introductie.html"
 *  tevens kun je viruele mappen nesten (Een virtuele map in een virtuele map) hierdoor kan een een hele map hergebuiken en parameterizeren.
 * DesignPattern: Chain of Responsibility & Command
 *
 * @package MVC
 */

abstract class VirtualFolder extends Object implements Command {

	/**
	 * Diepte van deze virtual folder.
	 * Als deze VirtualFolder de inhoud van de map "http://domain/folder/" afhandeld, dan $depth == 1.
	 * Een VirtualFolder van "http://domain/folder/subfolder/" heeft $depth == 2
	 * 
	 * @var int $depth  
	 */
	protected $depth;

	/**
	 * Het aantal niveau's(submappen) dat door deze VirtualFolder wordt afgehandeld.
	 * Deze variabele wordt gebruikt om de $depth van de submap uit te rekenen.
	 * Dit is handig als je een andere VirtualFolder wilt gebruiken terwijl je zelf al meerdere submappen gebruikt.
	 * Als je je de $depth_increment op 0 zet, dan wordt de andere VirtualFolder niet als subfolder,maar als de dezelfde folder gebruikt.  
	 * 
	 * @var int $depth_increment
	 */
	protected $depthIncrement = 1;

	protected $publicMethods;

	/**
	 * Bepaald of deze VirtualFolder bestandsnamen zonder extenties accepteerd.
	 * Als deze niet geaccepteerd worden(false), zal de bestandsnaam (via een redirect) omgezet worden naar een mapnaam.
	 *
	 * @var bool
	 */
	protected $handle_filenames_without_extension = false;

	/**
	 *
	 * @var VirtualFolder $parent  Deze virtuele map is een submap van ...
	 */
	private	$parent; 


	function __construct() {
		$methods = get_public_methods($this);
		foreach ($methods as $index => $method) {
			if (substr($method, 0, 1) == '_') {
				unset($methods[$index]); // Functies die beginnen met een "_" uit de publicMethods halen
			}
		}
		$this->publicMethods = array_diff($methods, array('execute', 'getPath', 'dynamicFilename', 'dynamicFoldername', 'onFileNotFound', 'onFolderNotFound', 'getDocument')); // Een aantal funties minder public maken
	}

	/**
	 * Aan de hand van de url de betreffende action functie aanroepen.
	 * Valt terug op dynamicFilename() en dynamicFoldername() functies, als de geen action functie gevonden wordt.
	 *
	 * @return Component
	 */
	function generateContent() {
		$this->initDepth();
		$path = URL::extract_path();
		$folders = $path['folders'];
		$filename = $path['filename'];
		$folder_count = count($folders);
		if ($folder_count == $this->depth) {
			$extension = file_extension($filename, $file);
			if ($extension === NULL && $this->handle_filenames_without_extension == false) { // Ongeldige bestandsnaam? (geen  extentie)
				error_log('filename without extension, redirecting to "'.$filename.'/"', E_NOTICE);
				redirect($filename.'/'); //	Redirect naar dezelfde url, maar dan als mapnaam
			}
			if ($this->publicMethods === null) {
				notice('Check if the parent::__construct() is called in '.get_class($this)."__construct()");
			}
			$function = str_replace('-', '_', $file);
			if (substr($function, -7) != '_folder' && in_array($function, $this->publicMethods)) {
				return $this->$function($extension); // Roept bijvoorbeeld de $this->index('html') functie aan.
			}
			return $this->dynamicFilename($filename);
		} 
		if ($folder_count > $this->depth) {
			if ($folder_count != ($this->depth + 1)) {
				$filename = false;; // Deze submap heeft nog 1 of meer submappen. 
			}
			$folder = $folders[$this->depth];
			$function = str_replace('-', '_', $folder).'_folder';
			if (in_array($function, $this->publicMethods)) {
				return $this->$function($filename); // Roept bijvoorbeeld de $this->fotos_folder('index.html') functie aan.
			}
			return $this->dynamicFoldername($folder, $filename);
		}
		warning('Not enough (virtual) subfolders in URI', 'VirtualFolder depth('.$this->depth.') exceeds maximum('.count($folders).')');
		return $this->onFolderNotFound(); // @todo eigen event?
	}
	
	/**
	 * Het pad opvragen van deze VirtualFolder
	 *
	 * @param bool $includeSubfolders  De actieve submap(pen) aan het path toevoegen (mappen die door deze VirtualFolder worden afgehandeld)
	 */
	function getPath($includeSubfolders = false) {
		$this->initDepth();
		$extractedPath = URL::extract_path();
		$folders = $extractedPath['folders'];
		$path = '/';
		for($i = 0; $i < $this->depth; $i++) {
			$path .= $folders[$i].'/';
		}
		if ($includeSubfolders) {
			for($i = $this->depth; $i < ($this->depth + $this->depthIncrement); $i++) {
				if (empty($folders[$i])) {
					break;
				}
				$path .= $folders[$i].'/';
			}
		}
		return $path;
	}

	/**
	 * Een bestand(snaam) afhandelen
	 *
	 * @param string $filename De bestandsnaam die in deze virtuele map word opgevraagd
	 * @return Component
	 */
	function dynamicFilename($filename) {
		if ($filename == 'index.html') {
			$command = new HttpError(403);
			$component = $command->execute();
			notice('No index() method configured for '.get_class($this), 'override VirtualFolder->index() or VirtualFolder->dynamicFilename()');
			return $component;
		}
		return $this->onFileNotFound();
	}

	/**
	 * Een submap afhandelen
	 *
	 * @param string $folder De submap die in deze virtuele map opgevraagd
	 * @param string|false $file Als er geen submap volgd, dan wordt $file de bestandsnaam binnen de map. Mocht je aan de hand van de mapnaam een nieuwe VirtualFolder starten, dan wordt de $file ook door de handle_file() afgehandeld.
	 * @return Component
	 */
	function dynamicFoldername($folder) {
		return $this->onFolderNotFound();
	}

	/**
	 * Event dat getriggert wordt als een (virtuele) bestand niet gevonden wordt.
	 * Geeft deze of een parent van deze virtualfolder de mogenlijkheid om een custom actie uit te voeren.
	 * 
	 * @return HttpError
	 */
	protected function onFileNotFound() {
		if ($this->parent !== null) {
			return $this->parent->onFileNotFound();
		}
		$relativePath = substr(rawurldecode(URL::info('path')), strlen(WEBPATH) - 1); // Relative path vanaf de WEBROOT
		notice('HTTP[404] File "'.$relativePath.'" not found');
		return new HttpError(404);

	}

	/**
	 * Event/Action voor het afhandelen van niet bestaande (virtuele) mappen.
	 * Geeft deze of een parent van deze virtualfolder de mogenlijkheid om een custom actie uit te voeren.
	 * 
	 * @return HttpError
	 */
	protected function onFolderNotFound() {
		if ($this->parent !== null) {
			return $this->parent->onFolderNotFound();
		}
		$relativePath = substr(rawurldecode(URL::info('path')), strlen(WEBPATH) - 1); // Relative path vanaf de WEBROOT
		$isFolder = (substr($relativePath, -1) == '/'); // Gaat de request om een folder?
		if ($isFolder) {
			$publicFolder = 'public'.$relativePath;
		} else {
			$publicFolder = 'public'.dirname($relativePath).'/';
		}
		// Zoek door de modules public mappen en kijk of de map bestaat.			
		$modules = SledgeHammer::getModules();
		foreach ($modules as $module) {
			if (is_dir($module['path'].$publicFolder)) { // Controleren of map bestaat in de public mappen
				if ($isFolder) {
					error_log('HTTP[403] Directory listing for "'.URL::uri().'" not allowed');
					return new HttpError(403);
				} else { // De map bestaat maar het bestand is niet gevonden.
					notice('HTTP[404] File "'.basename($relativePath).'" not found in "'.dirname($relativePath).'/"', 'VirtualFolder "'.get_class($GLOBALS['VirtualFolder']).'" doesn\'t handle the "'.basename($GLOBALS['VirtualFolder']->getPath(true)).'" folder');
					return new HttpError(404);
				}
			}
		}
		notice('HTTP[404] VirtualFolder "'.get_class($GLOBALS['VirtualFolder']).'" has no "'.basename($GLOBALS['VirtualFolder']->getPath(true)).'" folder');
		return new HttpError(404);
	}

	/**
	 * De VirtualFolder van een bepaalde class opvragen die zich hoger in de hierarchie bevind.
	 * 
	 * @return VirtualFolder
	 */
	function &getParentByClass($class) {
		if (strtolower(get_class($this)) == strtolower($class)) { // Is dit de gespecifeerde virtualfolder?
			return $this;
		} elseif ($this->parent === NULL) { // Is de virtualfolder niet gevonden in de hierarchie?
			$message = ($class === null) ? 'VirtualFolder "'.get_class($this).'" has no parent' : 'VirtualFolder \''.$class.'\' is niet actief';
			throw new Exception($message);
		}
		return $this->parent->getParentByClass($class);
	}

	/**
	 * Mits de $this->depth niet is ingesteld zal de waarde van $this->depth berekent worden.
	 * Hoe diep de handler genest is wordt aan de hand van de Parent->depth berekend.
	 * 
	 * @return int
	 */
	private function initDepth() {
		if ($this->depth !== NULL) { // Is de diepte reeds ingesteld?
			if (isset($GLOBALS['VirtualFolder']) == false) {
				if (($this instanceof Website) == false) {
					notice('VirtualFolder zonder Website object?');
				}
				$GLOBALS['VirtualFolder'] = &$this; // De globale pointer laten verwijzen naar deze 'virtuele map'
			}
			return;
		}
		if (isset($GLOBALS['VirtualFolder'])) {
			$this->parent = &$GLOBALS['VirtualFolder'];
		}
		$GLOBALS['VirtualFolder'] = &$this; // De globale pointer laten verwijzen naar deze 'virtuele map'
		if ($this->parent === NULL) { // Gaat het om de eerste VirtualFolder (Website)
			if (defined('WEBPATH')) {
				$this->depth = preg_match_all('/[^\/]+\//', WEBPATH, $match);
			} else {
				$this->depth = 0;
			}
		} else { // De VirtualFolder bevind zich in een ander  VirtualFolder
			$this->depth = $this->parent->depth + $this->parent->depthIncrement;
		}
	}
}
?>
