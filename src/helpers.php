<?php

use Sledgehammer\Mvc\View;

/**
 * Global function of the MVC module.
 * Functions that are available everywhere (global namespace).
 */

/**
 * render($view) is an alias to $view->render()
 * but render($view) generates a notice when the $view issn't a View compatible object instead of an fatal error.
 */
function render($view)
{
    if (Sledgehammer\is_valid_view($view)) {
        $view->render();
    }
}

/**
 * Check if the $view parameter is compatible with the View interface via ducktyping.
 *
 * @param View $view
 *
 * @return bool
 */
function is_view(&$view = '__UNDEFINED__')
{
    return is_object($view) && method_exists($view, 'render');
}

/**
 * Genereer een <script src=""> tag, mits deze al een keer gegenereerd is.
 *
 * @param string $src
 * @param string $identifier
 */
function javascript_once($src, $identifier = null)
{
    static $included = array();

    if ($identifier === null) {
        $identifier = $src;
    }
    if (isset($included[$identifier])) {
        return;
    }
    $included[$identifier] = true;
    echo '<script type="text/javascript" src="'.$src.'"></script>'."\n";
}
