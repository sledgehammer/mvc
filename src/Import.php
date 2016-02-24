<?php
/**
 * Import.
 */

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
    public function initial($value);

    /**
     * Returns the imported value.
     *
     * @param mixed $error
     * @param mixed $request
     *
     * @return mixed
     */
    public function import(&$error, $request = null);
}
