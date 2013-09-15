<?php
/**
 * PHeasel - a lightweight and simple PHP website development kit
 *
 * Copyright 2013 Jens Klingen
 *
 * For more information see: http://pheasel.org/
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once("../pheasel_config.php");
require_once("globals.php");
require_once("classes/SiteConfig.php");
require_once("classes/RequestHandler.php");

clear_export_dir();
$all_uris = SiteConfig::get_instance()->get_all_page_uris();
RequestHandler::get_instance()->batch_mode = true;
foreach($all_uris as $uri) {
    $rh = new RequestHandler();
    $rh->batch_mode = true;
    $markup = $rh->render_page($uri);
    if(!file_exists(PHEASEL_EXPORT_DIR.$uri)) mkdir(PHEASEL_EXPORT_DIR.$uri);
    file_put_contents(PHEASEL_EXPORT_DIR.$uri."index.html", $markup);
}
RequestHandler::get_instance()->batch_mode = false;
copy_static_dir();

function clear_export_dir() {
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PHEASEL_EXPORT_DIR, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
        $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
    }
}

function copy_static_dir() {
    foreach (
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(PHEASEL_PAGES_DIR . DIRECTORY_SEPARATOR . 'static', RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        if ($item->isDir()) {
            mkdir(PHEASEL_EXPORT_DIR . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . $iterator->getSubPathName(), 666, true);
        } else {
            copy($item, PHEASEL_EXPORT_DIR . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
}