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

require_once 'globals.php';
require_once(PHEASEL_ROOT . "/lib/log4php/Logger.php");
require_once 'PHPUnit/Autoload.php';
require_once PHEASEL_ROOT . '/../pheasel-config.php';

class PheaselTestCase extends PHPUnit_Framework_TestCase {

    protected $logger;

    function __construct() {
        Logger::configure(PHEASEL_ROOT.'/log4php.php');
        $this->logger = Logger::getLogger(get_class($this));
    }
}

?>