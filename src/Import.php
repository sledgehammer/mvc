<?php

namespace Sledgehammer\Mvc;

/**
 * Iterface for importing request data.
 *
 * @example
 * $data = $import->import($error);
 * if ($error) {
 *   // report error
 * } else {
 *  // do something
 * }
 */
interface Import
{
    /**
     * Set the (default) value.
     */
    public function setValue($value);

    /**
     * Get the current value.
     */
    public function getValue();

    /**
     * Import the new value based on the request and return the current value;.
     *
     * @param mixed $error   Contains the error code(s) in ALL_CAPS
     * @param mixed $request Allow overwriting the default $_REQUEST
     *
     * @return mixed The (updated) value
     */
    public function import(&$error = null, $request = null);
}
