<?php

use Sledgehammer\Mvc\Component\Button;
use Sledgehammer\Core\Html;
use Sledgehammer\Core\Url;

echo "<div class=\"modal-dialog\">\n";
echo "    <div class=\"modal-content\">\n";
echo "        <div class=\"modal-header\">";
echo '<h4 class="modal-title">';
echo Html::escape($title), "</h4></div>\n";
echo "        <div class=\"modal-body\">\n";
echo "            ", $body, "\n";
echo "        </div>\n";
if (count($choices) !== 0) {
    echo "        <form class=\"modal-footer\" action=\"".Url::getCurrentURL().'" method="'.$method."\">\n";
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
        echo "            ", $button, "\n";
    }
    echo "        </form>\n";
}
echo "    </div>\n";
echo "</div>\n";
