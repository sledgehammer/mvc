<?php

namespace Sledgehammer\Mvc;

use Exception;
use Sledgehammer\Core\Debug\DebugR;
use Sledgehammer\Mvc\Component\HttpError;
use Sledgehammer\Mvc\Document\Html as HtmlDocument;

/**
 * Superclass for the Website classes.
 * DesignPatterns: FrontController, Command, Chain of Responsibility.
 */
abstract class Website extends VirtualFolder
{
    public function __construct()
    {
        parent::__construct();
        $this->publicMethods = array_diff($this->publicMethods, array('handleRequest', 'generateDocument', 'statusbar', 'initLanguage', 'isWrapable')); // Een aantal functies *niet* public maken
    }

    /**
     * Send a response based on the request.
     */
    public function handleRequest()
    {
        // Build document
        $document = $this->generateDocument();
        if (!defined('Sledgehammer\GENERATED')) {
            define('Sledgehammer\GENERATED', microtime(true));
        }
        // Send headers
        $headers = $document->getHeaders();
        \Sledgehammer\send_headers($headers['http']);
        // Send the sledgehammer-statusbar as DebugR header.
        if (DebugR::isEnabled()) {
            ob_start();
            statusbar();
            DebugR::send('sledgehammer-statusbar', ob_get_clean(), true);
        }
        // Send the contents
        $document->render();
    }

    /**
     * Generate a Document for this request.
     *
     * @return Document
     */
    public function generateDocument()
    {
        try {
            $content = $this->generateContent();
        } catch (Exception $exception) {
            $content = new HttpError(500, array('exception' => $exception));
        }
        $isDocument = false;
        if (method_exists($content, 'isDocument')) {
            $isDocument = $content->isDocument();
        }
        if ($isDocument) {
            return $content;
        }
        $document = new HtmlDocument();
        $document->content = $this->wrapContent($content);

        return $document;
    }

    /**
     * Embed the component inside your Layout View.
     *
     * @return Component
     */
    abstract protected function wrapContent($content);
}
