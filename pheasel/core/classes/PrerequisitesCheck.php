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
class PrerequisitesCheck {
    private $not_writable = array();
    private $mod_rewrite_found = false;

    private $mod_rewrite_works;

    function __construct() {
        $this->mod_rewrite_works = basename($_SERVER["PHP_SELF"]) != basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        foreach(apache_get_modules() as $mod) {
            if($mod=='mod_rewrite') {
                $this->mod_rewrite_found = true;
                break;
            }
        }

        if(!$this->is_dir_writable(PHEASEL_ROOT . DIRECTORY_SEPARATOR . "pheasel" . DIRECTORY_SEPARATOR . "cache")) {
            array_push($this->not_writable, PHEASEL_ROOT . DIRECTORY_SEPARATOR . "cache");
        }
        if(!$this->is_dir_writable(PHEASEL_ROOT . DIRECTORY_SEPARATOR . "pheasel" . DIRECTORY_SEPARATOR . "logs")) {
            array_push($this->not_writable, PHEASEL_ROOT . DIRECTORY_SEPARATOR . "logs");
        }
        if(!$this->is_dir_writable(PHEASEL_EXPORT_DIR)) {
            array_push($this->not_writable, PHEASEL_EXPORT_DIR);
        }
    }

    function is_dir_writable($dir) {
        $filename = $dir . DIRECTORY_SEPARATOR . "test.txt";
        $handle = @fopen($filename, 'w');
        if($handle) {
            fclose($handle);
            unlink($filename);
            return true;
        } else {
            return false;
        }


    }
    public function has_problems() {
        return !$this->mod_rewrite_found || !$this->mod_rewrite_works || count($this->not_writable) > 0;
    }

    public function get_problem_list() {
        ob_start(); ?>
        <style>li.ok {color:#060;}li.nok {color:#b00;}li.nok:before{content: 'ERROR: ';font-weight:bold;}</style>
        <ul class="config-problems">
            <li class="ok"><strong>Webserver</strong><br/>Your webserver is up and running</li>
            <li class="ok"><strong>PHP support</strong><br/>PHP is enabled</li>
            <?php if ($this->mod_rewrite_found) { ?>
                <li class="ok"><strong>mod_rewrite</strong><br/>mod_rewrite is enabled</li>
            <?php } else { ?>
                <li class="nok"><strong>mod_rewrite</strong><br/>mod_rewrite is not enabled</li>
            <?php } ?>

            <?php if ($this->mod_rewrite_works ) { ?>
                <li class="ok"><strong>mod_rewrite</strong><br/>is configured correctly</li>
            <?php } else { ?>
                <li class="nok"><strong>mod_rewrite</strong><br/>does not seem to work as expected. Please make sure that <code>AllowOverride</code> is set to <code>All</code>.</li>
            <?php } ?>

            <?php if (count($this->not_writable) == 0 ) { ?>
                <li class="ok"><strong>File permissions</strong><br/>are set up correctly</li>
            <?php } else { ?>
                <li class="nok">
                    <strong>File permissions</strong><br/> are not set up correctly, PHeasel (i.e. Apache web server) needs to be able to write to
                    <ul>
                        <?php foreach($this->not_writable as $dir) { ?>
                            <li><? echo $dir?></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
        <?php
        return ob_get_clean();
    }
}