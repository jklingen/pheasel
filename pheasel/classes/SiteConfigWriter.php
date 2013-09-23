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
            if(strpos($current,PHEASEL_PAGES_DIR . 'static') !== 0 && substr($current, -4) != ".ini" && substr($current, -8) != ".inc.php"  ) {
                $this->trace("Parsing potential markup file: $current");
                $this->parse_file($current);
            } else {
                $this->trace("Ignoring static file: $current");
            }
            $it->next();
        }
        $this->write_xml();
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
                    // TODO needs cleanup!
                    $attrs["file"] = str_replace(DIRECTORY_SEPARATOR, "/", substr($path, strlen(PHEASEL_PAGES_DIR))); // make sure the paths work in linux environment even when generated on windows pc
                    $this->debug("Markup file configuration found for ".$attrs["file"]);
                    $pathparts = explode("/",$attrs["file"]);
                    $filenameparts = explode(".", array_pop($pathparts));
                    $filenameparts_for_id = array(); // will generate ID from filename if none is defined in the file
                    array_pop($filenameparts); // we do not care about the extension
                    $type = NULL;
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

                    $target_node = NULL;
                    if($type=="page") $target_node = $this->pages_node;
                    else if($type=="snip") $target_node = $this->snippets_node;
                    else if($type=="tmpl") $target_node = $this->templates_node;
                    $my_node = $target_node->addChild("item"); //new SimpleXMLElement("<item/>");
                    foreach($attrs as $k=>$v) {
                        $my_node->addAttribute($k, $v);
                    }
                }
            }
        }
    }

    function write_xml() {
		$pi = pathinfo(PHEASEL_FILES_CACHE);
		if(!file_exists($pi['dirname'])) {
			$this->info("Cache directory does not exist - creating it at ".$pi['dirname']);
			mkdir($pi['dirname']);
		}
        $this->info("Writing markup cache to ".PHEASEL_FILES_CACHE);
        $this->root_node->asXml(PHEASEL_FILES_CACHE);
    }

}