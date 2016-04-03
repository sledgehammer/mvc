<?php

namespace Sledgehammer\Mvc\Document;

use Sledgehammer\Core\Object;
use Sledgehammer\Mvc\Component\HttpError;
use Sledgehammer\Mvc\Document;

/**
 * Send a file from the filesystem.
 * An MVC style implementation of the render_file() function.
 * 
 * Supports HTTP headers: "If-Modified-Since" and "If-None-Match".
 */
class File extends Object implements Document
{
    public $headers = array();

    private $filename,
        $error = false,
        $notModified = false,
        $etag,
        $fileContents;

    /**
     * @param array $options ['etag'=> bool, 'file_get_contents' => bool]
     */
    public function __construct($filename, $options = array('etag' => false))
    {
        $this->filename = $filename;
        $this->etag = array_value($options, 'etag');
        if (!file_exists($filename)) {
            if (basename($filename) == 'index.html') {
                $this->error = new HttpError(403);
            } else {
                $this->error = new HttpError(404);
            }

            return;
        }
        $last_modified = filemtime($filename);
        if ($last_modified === false) {
            $this->error = new HttpError(500);

            return;
        }
        if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER)) {
            $if_modified_since = strtotime(preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']));
            if ($if_modified_since >= $last_modified) { // Is the Cached version the most recent?
                $this->notModified = true;

                return;
            }
        }
        if ($this->etag) {
            $etag = md5_file($filename);
            if (array_value($_SERVER, 'HTTP_IF_NONE_MATCH') === $etag) {
                $this->notModified = true;

                return;
            }
            $this->headers[] = 'ETag: '.md5_file($filename);
        }
        $this->notModified = false;
        if (is_dir($filename)) {
            $this->error = new HttpError(403);

            return;
        }
        $this->headers['Content-Type'] = \Sledgehammer\mimetype($filename);
        $this->headers['Last-Modified'] = gmdate('r', $last_modified);
        $filesize = filesize($filename);
        if ($filesize === false) {
            $this->error = new HttpError(500);

            return;
        }
        $this->headers['Content-Length'] = $filesize; // @todo Detecteer bestanden groter dan 2GiB, deze geven fouten.
        if (array_value($options, 'file_get_contents')) {
            $this->fileContents = file_get_contents($filename);
        }
    }

    public function getHeaders()
    {
        if ($this->error) { // Is er een fout opgetreden?
            return $this->error->getHeaders();
        }
        if ($this->notModified) { // Is het bestand niet aangepast?
            return array('http' => array(
                'Status' => '304 Not Modified',
            ));
        }
        // Het bestand bestaat en kan verstuurd worden.
        return array('http' => $this->headers);
    }

    public function render()
    {
        if ($this->error) { // Is er een fout opgetreden?
            $this->error->render();

            return;
        }
        if ($this->notModified) { // Is het bestand niet aangepast?
            return; // De inhoud van het bestand NIET versturen
        }
        if ($this->fileContents !== null) {
            echo $this->fileContents;
        } else {
            readfile($this->filename);
        }
    }

    /**
     * @return bool
     */
    public function isDocument()
    {
        if ($this->error) {
            return false; // If the file doesn't exist, show the error message inside the view.
        }

        return true;
    }
}
