<?php

namespace Sledgehammer\Mvc;

use Exception;
use Sledgehammer\Core\Base;
use Sledgehammer\Core\Url;
use Sledgehammer\Mvc\Component\HttpError;
use Sledgehammer\Mvc\Document\Redirect;

/**
 * Superclasse van de Virtuele mappen.
 *  Door Folder creer je eenvoudig virtuele mappen en virtuele bestanden.
 *  Hierdoor heb je vrijheid in de paden die je gebruikt om de pagina's aan te duiden. I.p.v. "page.php?id=17" maak je "pages/introductie.html"
 *  tevens kun je viruele mappen nesten (Een virtuele map in een virtuele map) hierdoor kan een een hele map hergebuiken en parameterizeren.
 */
abstract class Folder extends Base implements Controller
{
    /**
     * Diepte van deze virtual folder.
     * Als deze VirtualFolder de inhoud van de map "http://domain/folder/" afhandeld, dan $depth == 1.
     * Een VirtualFolder van "http://domain/folder/subfolder/" heeft $depth == 2.
     *
     * @var int
     */
    protected $depth;

    /**
     * Het aantal niveau's(submappen) dat door deze VirtualFolder wordt afgehandeld.
     * Deze variabele wordt gebruikt om de $depth van de submap uit te rekenen.
     * Dit is handig als je een andere VirtualFolder wilt gebruiken terwijl je zelf al meerdere submappen gebruikt.
     * Als je je de $depth_increment op 0 zet, dan wordt de andere VirtualFolder niet als subfolder,maar als de dezelfde folder gebruikt.
     *
     * @var int
     */
    protected $depthIncrement = 1;

    /**
     * automatisch gegenereerde array die bepaald of een methode via een url aangeroepen.
     *
     * @var array
     */
    protected $publicMethods;

    /**
     * Bepaald of deze VirtualFolder bestandsnamen zonder extenties accepteerd.
     * Als deze niet geaccepteerd worden(false), zal de bestandsnaam (via een redirect) omgezet worden naar een mapnaam.
     *
     * @var bool
     */
    protected $handle_filenames_without_extension = false;

    /**
     * Deze virtuele map is een submap van ...
     *
     * @var Folder
     */
    private $parent;

    /**
     * The current active VirtualFolder (Is used to detect the parent).
     *
     * @var Folder
     */
    public static $current;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $methods = \Sledgehammer\get_public_methods($this);
        foreach ($methods as $index => $method) {
            if (substr($method, 0, 1) == '_') {
                unset($methods[$index]); // Functies die beginnen met een "_" uit de publicMethods halen
            }
        }
        $this->publicMethods = array_diff($methods, ['execute', 'getPath', 'file', 'folder', 'onFileNotFound', 'onFolderNotFound', 'getDocument']); // Prevent API methods from being called via a public url.
    }

    /**
     * Aan de hand van de url de betreffende action functie aanroepen.
     * Valt terug op file() en folder() functies, als de geen action functie gevonden wordt.
     *
     * @return Component
     */
    public function generateContent()
    {
        $this->initDepth();
        $url = Url::getCurrentURL();
        $folders = $url->getFolders();
        $filename = $url->getFilename();
        $folder_count = count($folders);
        if ($folder_count == $this->depth) {
            $extension = \Sledgehammer\file_extension($filename, $file);
            if ($extension === null && $this->handle_filenames_without_extension == false) { // Ongeldige bestandsnaam? (geen  extentie)
                error_log('filename without extension, redirecting to "'.$filename.'/"', E_NOTICE);

                return new Redirect($filename.'/'); // Redirect naar dezelfde url, maar dan als mapnaam
            }
            if ($this->publicMethods === null) {
                notice('Check if the parent::__construct() is called in '.get_class($this).'__construct()');
            }
            $function = str_replace('-', '_', $file);
            if (substr($function, -7) != '_folder' && in_array($function, $this->publicMethods)) {
                return $this->$function($extension); // Roept bijvoorbeeld de $this->index('html') functie aan.
            }

            return $this->file($filename);
        }
        if ($folder_count > $this->depth) {
            if ($folder_count != ($this->depth + 1)) {
                $filename = false; // Deze submap heeft nog 1 of meer submappen.
            }
            $folder = $folders[$this->depth];
            $function = str_replace('-', '_', $folder).'_folder';
            if (in_array($function, $this->publicMethods)) {
                return $this->$function($filename); // Roept bijvoorbeeld de $this->fotos_folder('index.html') functie aan.
            }

            return $this->folder($folder, $filename);
        }
        warning('Not enough (virtual) subfolders in URI', 'VirtualFolder depth('.$this->depth.') exceeds maximum('.count($folders).')');

        return $this->onFolderNotFound(); // @todo eigen event?
    }

    /**
     * Het pad opvragen van deze VirtualFolder.
     *
     * @param bool $includeSubfolders De actieve submap(pen) aan het path toevoegen (mappen die door deze VirtualFolder worden afgehandeld)
     */
    public function getPath($includeSubfolders = false)
    {
        $this->initDepth();
        $folders = Url::getCurrentURL()->getFolders();
        $path = '/';
        for ($i = 0; $i < $this->depth; ++$i) {
            $path .= $folders[$i].'/';
        }
        if ($includeSubfolders) {
            for ($i = $this->depth; $i < ($this->depth + $this->depthIncrement); ++$i) {
                if (empty($folders[$i])) {
                    break;
                }
                $path .= $folders[$i].'/';
            }
        }

        return $path;
    }

    /**
     * Een bestand(snaam) afhandelen.
     *
     * @param string $filename De bestandsnaam die in deze virtuele map word opgevraagd
     *
     * @return Component
     */
    public function file($filename)
    {
        if ($filename == 'index.html') {
            return new HttpError(403, ['notice' => [
                'No index() method configured for '.get_class($this),
                'override VirtualFolder->index() or VirtualFolder->file() in "'.get_class($this).'"',
            ]]);
        }

        return $this->onFileNotFound();
    }

    /**
     * Een submap afhandelen.
     *
     * @param string       $folder De submap die in deze virtuele map opgevraagd
     * @param string|false $file   Als er geen submap volgd, dan wordt $file de bestandsnaam binnen de map. Mocht je aan de hand van de mapnaam een nieuwe VirtualFolder starten, dan wordt de $file ook door de handle_file() afgehandeld.
     *
     * @return Component
     */
    public function folder($folder)
    {
        return $this->onFolderNotFound();
    }

    /**
     * Event dat getriggert wordt als een (virtuele) bestand niet gevonden wordt.
     * Geeft deze of een parent van deze virtualfolder de mogenlijkheid om een custom actie uit te voeren.
     *
     * @return HttpError
     */
    protected function onFileNotFound()
    {
        if ($this->parent !== null) {
            return $this->parent->onFileNotFound();
        }
        $relativePath = substr(rawurldecode(Url::getCurrentURL()->path), strlen(\Sledgehammer\WEBPATH) - 1); // Relative path vanaf de WEBROOT
        return new HttpError(404, ['notice' => 'HTTP[404] File "'.$relativePath.'" not found']);
    }

    /**
     * Event/Action voor het afhandelen van niet bestaande (virtuele) mappen.
     * Geeft deze of een parent van deze virtualfolder de mogenlijkheid om een custom actie uit te voeren.
     *
     * @return HttpError
     */
    protected function onFolderNotFound()
    {
        if ($this->parent !== null) {
            return $this->parent->onFolderNotFound();
        }
        $url = Url::getCurrentURL();
        $relativePath = substr(rawurldecode($url->path), strlen(\Sledgehammer\WEBPATH) - 1); // Relative path vanaf de WEBROOT
        $isFolder = (substr($relativePath, -1) == '/'); // Gaat de request om een folder?
        if ($isFolder) {
            $folder = $relativePath;
        } else {
            $folder = dirname($relativePath).'/';
        }
//        $publicFolder = array(APP_DIR.'/public'.$folder);
//        $folders = explode('/', substr($folder, 1, -1));
//        if (count($folders) != 0) {
//            $module = $folders[0];
//            $modules = Framework::getModules();
//            if (isset($modules[$module])) {
//                $publicFolder[] = $modules[$module]['path'].'public'.substr($folder, strlen($module) + 1);
//            }
//        }
        // Zoek door de public mappen en kijk of de map/bestand bestaat.
        $foundPublicFolder = false;
//        foreach ($publicFolder as $folder) {
//            if (is_dir($folder)) {
//                $foundPublicFolder = $folder;
//                if ($isFolder) {
//                    error_log('HTTP[403] Directory listing for "'.$url.'" not allowed');
//
//                    return new HttpError(403);
//                }
//            }
//            $path = $isFolder ? $folder : $folder.basename($relativePath);
//            if (file_exists($path)) {
//                if (is_readable($path)) {
//                    return new HttpError(500, array('warning' => 'render_public_folder.php should have renderd the file: "'.$path.'"'));
//                } else {
//                    return new HttpError(403, array('warning' => 'Permission denied for file "'.basename($path).'" in "'.dirname($path).'/"'));
//                }
//            }
//        }
        if ($foundPublicFolder) {
            return new HttpError(404, ['notice' => [
                'HTTP[404] File "'.basename($relativePath).'" not found in "'.dirname($relativePath).'/"',
                'VirtualFolder "'.get_class(self::$current).'" doesn\'t handle the "'.basename(self::$current->getPath(true)).'" folder',
            ]]);
        }
        // Gaat om een bestand in een virtualfolder
        return new HttpError(404, ['notice' => 'HTTP[404] VirtualFolder "'.get_class(self::$current).'" has no "'.basename(self::$current->getPath(true)).'" folder']);
    }

    /**
     * De VirtualFolder van een bepaalde class opvragen die zich hoger in de hierarchie bevind.
     *
     * @return Folder
     */
    public function &getParentByClass($class)
    {
        if (strtolower(get_class($this)) == strtolower($class)) { // Is dit de gespecifeerde virtualfolder?
            return $this;
        }
        if ($this->parent === null) { // Is de virtualfolder niet gevonden in de hierarchie?
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
    private function initDepth()
    {
        if ($this->depth !== null) { // Is de diepte reeds ingesteld?
            return;
        }
        if (isset(self::$current) == false) { // Gaat het om de eerste VirtualFolder (Website)
            if (($this instanceof Website) == false) {
                notice('VirtualFolder outside a Website object?');
            }
            self::$current = &$this; // De globale pointer laten verwijzen naar deze 'virtuele map'
            if (defined('Sledgehammer\WEBPATH')) {
                $this->depth = preg_match_all('/[^\/]+\//', \Sledgehammer\WEBPATH, $match);
            } else {
                $this->depth = 0;
            }

            return;
        }
        $this->parent = &self::$current;
        self::$current = &$this; // De globale pointer laten verwijzen naar deze 'virtuele map'
        $this->depth = $this->parent->depth + $this->parent->depthIncrement;
    }
}
