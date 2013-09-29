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

require_once(PHEASEL_ROOT . '/classes/AbstractLoggingClass.php');
require_once(PHEASEL_ROOT . '/includes/util.php'); // TODO understand path issue
require_once(PHEASEL_ROOT . '/classes/SiteConfig.php');
require_once(PHEASEL_ROOT . '/classes/domain/Placeholder.php');

class SiteConfigWriter extends AbstractLoggingClass {

    static private $unique_instance = null;

    static public function get_instance() {
        if (null === self::$unique_instance) {
            self::$unique_instance = new self;
        }
        return self::$unique_instance;
    }

    private $root_node;
    private $pages_node;
    private $templates_node;
    private $snippets_node;
    private $page_xmls;
    private $template_xmls;
    private $snippet_xmls;



    function update_cache() {
        $this->info("Updating markup cache at ".PHEASEL_FILES_CACHE." from files in ".PHEASEL_PAGES_DIR);
        $cdate = date("Y-m-d H:i:s");
        $this->root_node = new SimpleXMLElement("<pheasel/>");
        $this->root_node->addAttribute("created",$cdate);

        $this->pages_node = $this->root_node->addChild("pages");
        $this->snippets_node = $this->root_node->addChild("snippets");
        $this->templates_node = $this->root_node->addChild("templates");

        $this->page_xmls = array();
        $this->template_xmls = array();
        $this->snippet_xmls = array();
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PHEASEL_PAGES_DIR, FilesystemIterator::SKIP_DOTS));
        $it->next();
        while($it->valid()) {
            $current = $it->current();
            // no need to process static directory
            if(strpos($current,PHEASEL_PAGES_DIR . 'static') !== 0 && $this->has_valid_markup_file_extension($current)) {
                $this->trace("Parsing potential markup file: $current");
                $this->parse_file($current);
            } else {
                $this->trace("Ignoring static file or file with wrong extension: $current");
            }
            $it->next();
        }
        $this->write_xml();
    }

    /**
     * Valid markup file extensions are .php (but not .inc.php), .htm, .html, .css and .js
     * @param $filename string filename to check
     * @return bool whether the filename has one of the valid extensions for markup files
     */
    function has_valid_markup_file_extension($filename) {
        $last4 = substr($filename, -4);
        if($last4 == '.php' && substr($filename, -8) != ".inc.php") return true;
        if($last4 == '.htm' || $last4 == '.css') return true;
        if(substr($filename, -5) == '.html') return true;
        if(substr($filename, -3) == '.js') return true;
        return false;
    }

    function parse_file($path) {
        $attrs = null;

        $file_content = file_get_contents($path);

        if(preg_match_all(PHEASEL_PLACEHOLDER_REGEX, $file_content, $placeholders)) {
            //global $pages_node;
            //$page_node = NULL;
            foreach($placeholders[1] as $placeholder) {

                $ph = new Placeholder($placeholder); // 1st group should be without prefix and suffix
                $attrs = $ph->attributes;
                if($ph->name=='config' && !is_null($attrs)) {
                    $relative_path = substr($path, strlen(PHEASEL_PAGES_DIR));
                    $attrs["file"] = str_replace(DIRECTORY_SEPARATOR, "/", $relative_path); // make sure the paths work in linux environment even when generated on windows pc
                    $this->debug("Markup file configuration found for ".$attrs["file"]);

                    $pathparts = explode("/",$attrs["file"]);
                    $filenameparts = explode(".", array_pop($pathparts));

                    $filenameparts_for_id = array(); // will generate ID from filename if none is defined in the file
                    array_pop($filenameparts); // we do not care about the extension for the ID

                    // populate some markup config parameters from filename (language and markup type)
                    $type = null;
                    foreach($filenameparts as $i=>$filenamepart) {
                        if(empty($attrs["lang"]) && strlen($filenamepart) == 2) { // 2 chars is assumed to be language (if not already set)
                            $attrs["lang"] = $filenamepart;
                        } else if(strlen($filenamepart) == 4 && ($filenamepart == 'tmpl' || $filenamepart == 'snip' || $filenamepart == 'page')) {
                            $type = $filenamepart;
                        } else {
                            array_push($filenameparts_for_id, $filenamepart);
                        }
                    }
                    if(empty($attrs["id"])) { // if empty, generate id from folder and filename
                        $attrs["id"] = "";
                        if(count($pathparts)>0) $attrs["id"] .= implode(".", $pathparts) . ".";
                        $attrs["id"] .= implode(".", $filenameparts_for_id);
                    }
                    $attrs["modified"] = date("Y-m-d H:i:s", filemtime($path));

                    $file_registered = false;
                    if(empty($attrs["lang"])) {
                        $pi = pathinfo($path);
                        $glob_pattern = $pi['dirname'] . DIRECTORY_SEPARATOR . $pi['filename'] . '.??.ini';
                        $this->trace("Looking for L10N ini files: " . $glob_pattern);
                        foreach(glob($pi['dirname'] . DIRECTORY_SEPARATOR . $pi['filename'] . '.??.ini', GLOB_NOSORT ) as $inifile) {
                            $ini_attrs = $this->extend_attrs_from_ini($attrs, $inifile);
                            $this->debug("Registering markup file with INI localisation" .$ini_attrs["file"] . "(".$pi['filename'].")");
                            $this->create_markup_file_node($type, $ini_attrs);
                            $file_registered = true;
                        }
                    }
                    // if there is no localisation, we assume that there is nothing to localise and accept missing lang
                    if(!$file_registered) {
                        $this->debug("Registering localised markup file " . $attrs["file"]);
                        $this->create_markup_file_node($type, $attrs);
                    }

                }
            }
        }
    }

    /**
     * Extends procided attributes with config parameters from INI file
     * @param $attrs array attributes as loaded from markup file
     * @param $inifile string the ini file to extend from
     * @return array extended attributes
     */
    private function extend_attrs_from_ini($attrs, $inifile)
    {
        $ini_attrs = $attrs;
        $parsed_ini = try_parse_ini($inifile);
        $ini_config = get_from_array($parsed_ini, 'config');
        if ($ini_config) {
            $this->debug("Found config section");
            $url = get_from_array($ini_config, 'url');
            $name = get_from_array($ini_config, 'name');

            if (!isset($ini_attrs['url'])) $ini_attrs['url'] = $url;
            if (!isset($ini_attrs['name'])) $ini_attrs['name'] = $name;

            $ini_name_parts = explode('.', $inifile);
            $ini_attrs['lang'] = $ini_name_parts[count($ini_name_parts) - 2];
            return $ini_attrs;
        }
        return $ini_attrs;
    }

    /**
     * Creates a child node depending on type and adds attributes to it.
     * @param $type string page|snip|tmpl
     * @param $attrs array of attributes for the markup file
     * @throws Exception if type cannot be processed
     */
    private function create_markup_file_node($type, $attrs) {
        if ($type == "page") $my_node = $this->pages_node->addChild("item");
        else if ($type == "snip") $my_node = $this->snippets_node->addChild("item");
        else if ($type == "tmpl") $my_node = $this->templates_node->addChild("item");
        else throw new Exception("Markup type $type could not be processed. The file name should contain either page, tmpl or snip.");

        foreach ($attrs as $k => $v) {
            $my_node->addAttribute($k, $v);
        }
    }

    function write_xml() {
        $this->info("Writing markup cache to ".PHEASEL_FILES_CACHE);
        $this->root_node->asXml(PHEASEL_FILES_CACHE);
    }

}