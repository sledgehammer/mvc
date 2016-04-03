<?php

namespace Sledgehammer\Mvc;

/**
 * Interface for Controllers, the C in MVC.
 */
interface Controller
{
    /**
     * Build and return a component object.
     *
     * @return Component
     */
    public function generateContent();
}
