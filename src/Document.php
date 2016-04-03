<?php

namespace Sledgehammer\Mvc;

/**
 * A Document is a standalone component, that can't be wrapped inside another component.
 * Example documents are: Json, Document\File, Image and Document\Html.
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
