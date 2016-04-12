<?php

namespace Sledgehammer\Mvc;

/**
 * A Document is a standalone component, that can't be wrapped inside another component.
 * Example documents are: Sledgehammer\Core\Json, Sledgehammer\Mvc\Document\File, Sledgehammer\Graphics\Image and Sledgehammer\Mvc\Document\Page.
 */
interface Document extends Component
{
    /**
     * Determines if the component is a Document.
     * This allows errors to be wrapped in a layout.
     *
     * @return bool
     */
    public function isDocument();

    /**
     * The headers for this type of document.
     * Must include 'http' headers.
     *
     * @return array
     */
    public function getHeaders();
}
