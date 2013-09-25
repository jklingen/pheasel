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
 * convenience: dumps a variable into HTML &lt;pre&gt; tags
 * @param $var variable to dump
 */
function dump($var) {
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
}

/**
 * @param $haystack string that might end with $needle
 * @param $needle string that might be at the end of $haystack
 * @return bool true if $haystack ends with $needle, false otherwise
 */
function str_ends_with($haystack, $needle) {
    return substr($haystack, -strlen($needle))===$needle;
}

/**
 * @param $name string name of the cookie to retrieve
 * @return string value of the requested cookie, or null if it does not exist
 */
function get_cookie($name) {
    return get_from_array($_COOKIE, $name);
}

/**
 * retrieves named element from array
 * @param $array array to retrieve the element from
 * @param $name string name of the element to retrieve
 * @param null $default default value to be returned if the element does not exist, defaults to null
 * @return mixed value of the array element, or $default if it does not exist
 */
function get_from_array($array, $name, $default = null) {
    return isset($array[$name]) ? $array[$name] : $default;
}