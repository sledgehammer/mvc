<?php

namespace Sledgehammer\Mvc;

/**
 * Interface for the views, the V in MVC.
 */
interface View
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
