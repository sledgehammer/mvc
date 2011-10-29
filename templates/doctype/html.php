<?php
/**
 * HTML5
 */
?><!DOCTYPE html>
<html<?php echo $htmlParameters; ?>>
<head>
	<title><?php echo $title; ?></title>
<?php 
foreach ($head as $html) {
	echo "\t".$html."\n";
}
?>
</head>
<?php flush(); ?>
<body<?php echo $bodyParameters; ?>>

<?php render($body); ?>
<?php if ($showStatusbar): ?>


<div class="statusbar" id="statusbar">
	<a href="javascript:document.getElementById('statusbar').style.display='none';" title="Hide statusbar" style="float:right;margin-right: 4px; font: 14px sans-serif; text-decoration: none;">&#10062;</a>
	<?php echo $GLOBALS['website']->statusbar(); ?>
</div>
<?php endif; ?>

</body>
</html>