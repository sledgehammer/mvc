<?php

namespace Sledgehammer\Mvc;

/**
 * Interface for the components, the V in MVC.
 */
interface Component
{
    /**
     * Render the view to the client (echo statements).
     */
    public function render();

    /*
     * (Optional method)
     * An array with view dependencies, that should be sent in the HTTP header or inside <head> tag.
     *
     * @return array
     */
    //function getHeaders();
}
