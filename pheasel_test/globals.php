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

// pheasel working directory
define('PHEASEL_ROOT', realpath(getcwd() . "/../pheasel"));

// user's content space
define('PHEASEL_PAGES_DIR', realpath(PHEASEL_ROOT . "/../pheasel_test/test-site").DIRECTORY_SEPARATOR); // force trailing slash, which makes things easier for us later

// pheasel's internal files cache
define('PHEASEL_FILES_CACHE', realpath(PHEASEL_ROOT . '/../pheasel_test/cache/files.xml'));

// pheasel export directory for page-wise HTML/PHP export
define('PHEASEL_EXPORT_DIR', realpath(PHEASEL_ROOT . "/../site-export"));

if(strpos(PHEASEL_PAGES_DIR, realpath(PHEASEL_ROOT."/../")) === false) {
    throw new Exception("Directory structure does not seem to be valid - is the 'site' subdirectory missing?");
}
if(strpos(PHEASEL_EXPORT_DIR, realpath(PHEASEL_ROOT."/../")) === false) {
    throw new Exception("Directory structure does not seem to be valid - is the 'site-export' subdirectory missing?");
}

