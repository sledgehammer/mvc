<?php
/**
 * Controller.
 */

namespace Sledgehammer\Mvc;

/**
 * Interface for Controllers, the C in MVC.
 */
interface Controller
{
    /**
     * Build and return a view object.
     *
     * @return View
     */
    public function generateContent();
}
