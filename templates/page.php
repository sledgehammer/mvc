<!DOCTYPE html>
<html<?php echo $htmlParameters; ?>>
<head>
    <title><?php echo $title; ?></title>
<?php
foreach ($head as $html) {
    echo "    ".$html."\n";
}
?>
</head>
<?php flush(); ?>
<body<?php echo $bodyParameters; ?>>

<?php render($body); ?>
<?php if ($statusbar) {
    include(__DIR__.'/../statusbar.php');
} ?>

</body>
</html>
