<?php
/**
 * XHTML 1.1 Strict
 */ 
echo '<?xml'; ?> version="1.0" encoding="<?php echo $charset; ?>"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl">
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
	<a href="javascript:document.getElementById('statusbar').style.display='none';" title="Hide statusbar" class="statusbar_hide" style="float:right;">&#10062;</a>
	<?php echo $GLOBALS['website']->statusbar(); ?>
</div>
<?php endif; ?>

</body>
</html>

