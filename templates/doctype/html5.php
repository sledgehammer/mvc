<?php
/**
 * HTML5
 */
?><!DOCTYPE html>
<html<?php echo $html; ?>>
<head>
    <title><?php echo $title; ?></title>
<?php
    foreach ($head as $html) {
        echo "    " . $html . "\n";
    }
?>
</head>
<?php flush(); ?>
<body<?php echo $body; ?>>

<?php render($content); ?>

<?php
    if ($statusbarVisible) {
        include(__DIR__ . '/../statusbar.php');
    }
?>

</body>
</html>
