<?php

use Sledgehammer\Mvc\Component\Button;
use Sledgehammer\Core\Html;
use Sledgehammer\Core\Url;

echo "<div class=\"modal-dialog\">\n";
echo "\t<div class=\"modal-content\">\n";
echo "\t\t<div class=\"modal-header\">";
echo '<h4 class="modal-title">';
echo Html::escape($title), "</h4></div>\n";
echo "\t\t<div class=\"modal-body\">\n\t\t\t", $body, "\n\t\t</div>\n";
if (count($choices) !== 0) {
    echo "\t\t<form class=\"modal-footer\" action=\"".Url::getCurrentURL().'" method="'.$method."\">\n";
    $indexed = \Sledgehammer\is_indexed($choices);
    foreach (array_reverse($choices) as $answer => $choice) {
        if (is_array($choice) === false) {
            $choice = ['label' => $choice];
        }
        $choice['type'] = 'submit';
        $choice['name'] = $identifier;
        if ($indexed) {
            $choice['value'] = $choice['label'];
        } else {
            $choice['value'] = $answer;
        }
        $button = new Button($choice);
        echo "\t\t\t", $button, "\n";
    }
    echo "\t\t</form>\n";
}
echo "\t</div>\n";
echo "</div>\n";
