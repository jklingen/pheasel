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

/**
 * Class Placeholder represents a placeholder string, e.g. <code>&lt;ph:asdf qwer=bla/&gt;</code>
 * where $name would be "asdf"
 * and $attributes an array like [qwer:"bla"]
 * Uses PHP's {@link  parse_ini_string} internally (adding newline before e.g. qwer=), so the same
 * rules apply for special characters. If you want sections to be parsed, pass the $section_mode
 * argument to the constructor.
 */
class Placeholder {

    const SECTION_MODE_IGNORE = 0;
    const SECTION_MODE_PROCESS = 1;

    /**
     * @var string name of the Placeholder, e.g. snippet
     */
    public $name;
    /**
     * @var array array of the placeholder's attributes
     */
    public $attributes;

    /**
     * @var string the original string that has been parsed to populate this object
     */
    public $placeholder_string;

    /**
     * @param $placeholder_string string placeholder string with or without prefix/suffix, e.g. <code><ph:asdf qwer=bla/></code>
     * @param int $section_mode either Placeholder::SECTION_MODE_PROCESS or Placeholder::SECTION_MODE_IGNORE
     */
    function __construct($placeholder_string, $section_mode = Placeholder::SECTION_MODE_IGNORE) {
        $this->placeholder_string = $placeholder_string;
        $placeholder_string = trim($placeholder_string);
        // get rid of <ph: and />
        $placeholder_string = str_replace(PHEASEL_PLACEHOLDER_PREFIX, "", $placeholder_string);
        $placeholder_string = str_replace(PHEASEL_PLACEHOLDER_SUFFIX, "", $placeholder_string);
        $exp = preg_split('/\s+/', $placeholder_string, 2);
        $this->name = $exp[0];
        if(count($exp)>1) {
            $this->attributes = Placeholder::parse_inline_ini_string($exp[1], $section_mode);
        }
    }

    /**
     * Same as {@link parse_ini_string}, but does not require line breaks, e.g. <code>asdf=</code> will then be handled like <code>\nasdf=</code>
     * @param $ini_string string INI formatted string which might omit line breaks
     * @param int $section_mode either Placeholder::SECTION_MODE_PROCESS or Placeholder::SECTION_MODE_IGNORE
     * @return array parsed settings from ini string
     */
    static function parse_inline_ini_string($ini_string, $section_mode = Placeholder::SECTION_MODE_IGNORE) {
        // insert newline before any asdf= or [asdf]
        $ini_string_with_breaks = preg_replace('/([A-Za-z0-9\.\-_:]+?=|\[\.+?])/', "\n$1", $ini_string);
        return parse_ini_string($ini_string_with_breaks,($section_mode==Placeholder::SECTION_MODE_PROCESS));
    }

}