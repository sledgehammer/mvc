<?php

use Sledgehammer\Mvc\Component;

/**
 * Global function of the MVC module.
 * Functions that are available everywhere (global namespace).
 */

/**
 * render($component) is an alias to $component->render()
 * but render($component) generates a notice when the $component isn't a component compatible object instead of an fatal error.
 */
function render($component)
{
    if (Sledgehammer\is_valid_component($component)) {
        $component->render();
    }
}

/**
 * Check if  $component is compatible with the Component interface via ducktyping.
 *
 * @param Component $component
 *
 * @return bool
 */
function is_component(&$component = '__UNDEFINED__')
{
    return is_object($component) && method_exists($component, 'render');
}

/**
 * Genereer een <script src=""> tag, mits deze al een keer gegenereerd is.
 *
 * @param string $src
 * @param string $identifier
 */
function javascript_once($src, $identifier = null)
{
    static $included = [];

    if ($identifier === null) {
        $identifier = $src;
    }
    if (isset($included[$identifier])) {
        return;
    }
    $included[$identifier] = true;
    echo '<script type="text/javascript" src="'.$src.'"></script>'."\n";
}
