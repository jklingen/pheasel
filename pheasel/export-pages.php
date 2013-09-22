<!DOCTYPE html>
<!--
PHeasel - a lightweight and simple PHP website development kit

Copyright 2013 Jens Klingen

For more information see: http://pheasel.org/

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
-->
<html>
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="resources/favicon.ico"/>

</head>
<body><pre>
<?php
require_once("../pheasel_config.php");
require_once("globals.php");
require_once("includes/util.php");
require_once("classes/SiteConfig.php");
require_once("classes/RequestHandler.php");

$mode = get_from_array($_GET, 'mode');
$export_single = get_from_array($_GET, 'exportsingle');
$export_all = get_from_array($_GET, 'exportall');

if($mode=='PHP') {
    $preserve_php = true;
    $ext = 'php';
} else {
    $preserve_php = false;
    $ext = 'html';
}

if($export_single) {
    $urls = array($export_single);
} else {
    $urls = SiteConfig::get_instance()->get_all_page_urls();
    clear_export_dir();
}

echo "Starting export of ".count($urls)." page(s) as $mode.\n";

foreach($urls as $url) {
    echo " * $url";
    $rh = new RequestHandler(); // do not use the singleton, but a fresh RequestHandler for every page
    $rh->preserve_php = $preserve_php;
    $rh->batch_mode = true;
    $markup = $rh->render_page($url);
    if(!file_exists(PHEASEL_EXPORT_DIR.$url)) mkdir(PHEASEL_EXPORT_DIR.$url);
    file_put_contents(PHEASEL_EXPORT_DIR.$url."index.$ext", $markup);
}

copy_static_dir();
echo "<strong>Successfully exported to ".PHEASEL_EXPORT_DIR."</strong>\n";

function clear_export_dir() {
    echo "Clearing export directory: ".PHEASEL_EXPORT_DIR."\n";
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PHEASEL_EXPORT_DIR, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
        $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
    }
}

function copy_static_dir() {
    echo "Copying static resources.\n";
    foreach (
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(PHEASEL_PAGES_DIR . DIRECTORY_SEPARATOR . 'static', RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        $dir_full_path = PHEASEL_EXPORT_DIR . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        if ($item->isDir()) {
            if(!file_exists($dir_full_path)) mkdir($dir_full_path, 666, true);
        } else {
            copy($item, $dir_full_path);
        }
    }
}
?></pre></body></html>