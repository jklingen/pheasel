<?php
require_once("../../globals.php");

$not_writable = array();
$mod_rewrite_found = false;


foreach(apache_get_modules() as $mod) {
    if($mod=='mod_rewrite') {
        $mod_rewrite_found = true;
        break;
    }
}

function is_dir_writable($dir) {
    $filename = $dir . DIRECTORY_SEPARATOR . "test.txt";
    $handle = fopen($filename, 'w') or die("can't open file");
    if($handle) {
        fclose($handle);
        unlink($filename);
        return true;
    } else {
        return false;
    }
}


if(!is_dir_writable(PHEASEL_ROOT . DIRECTORY_SEPARATOR . "cache")) {
    array_push($not_writable, PHEASEL_ROOT . DIRECTORY_SEPARATOR . "cache");
}
if(!is_dir_writable(PHEASEL_ROOT . DIRECTORY_SEPARATOR . "logs")) {
    array_push($not_writable, PHEASEL_ROOT . DIRECTORY_SEPARATOR . "logs");
}
if(!is_dir_writable(PHEASEL_EXPORT_DIR)) {
    array_push($not_writable, PHEASEL_EXPORT_DIR);
}


?>


<ul>
    <li class="ok"><strong>Webserver</strong><br/>Your webserver is up and running</li>
    <li class="ok"><strong>PHP support</strong><br/>PHP is enabled</li>
    <?php if ($mod_rewrite_found) { ?>
        <li class="ok"><strong>mod_rewrite</strong><br/>mod_rewrite is enabled</li>
    <?php } else { ?>
        <li class="nok"><strong>mod_rewrite</strong><br/>mod_rewrite is not enabled</li>
    <?php } ?>

    <?php if (__FILE__ != 'index.php') { ?>
        <li class="ok"><strong>mod_rewrite</strong><br/>is configured correctly</li>
    <?php } else { ?>
        <li class="nok"><strong>mod_rewrite</strong><br/>does not seem to work as expected. Please make sure that <code>AllowOverride</code> is set to <code>All</code></li>
    <?php } ?>

    <?php if (count($not_writable) == 0 ) { ?>
        <li class="ok"><strong>File permissions</strong><br/>are set up correctly</li>
    <?php } else { ?>
        <li class="nok">
            <strong>File permissions</strong><br/> are not set up correctly, PHeasel (i.e. Apache web server) needs to be able to write to
            <ul>
            <?php foreach($not_writable as $dir) { ?>
                <li><?=$dir?></li>
            <?php } ?>
            </ul>
        </li>
    <?php } ?>

</ul>