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

require_once(PHEASEL_ROOT . "/includes/util.php"); // TODO understand path issue
require_once(PHEASEL_ROOT . "/classes/AbstractLoggingClass.php");
require_once(PHEASEL_ROOT . "/classes/domain/PageInfo.php");
require_once(PHEASEL_ROOT . "/classes/domain/TemplateInfo.php");
require_once(PHEASEL_ROOT . "/classes/domain/SnippetInfo.php");
require_once(PHEASEL_ROOT . "/classes/error/MarkupNotFoundException.php");
require_once(PHEASEL_ROOT . "/classes/error/AmbiguosConfigException.php");


/**
 * Class SiteConfig parses the xml files cache and provides information about available templates, pages and snippets.
 */
class SiteConfig extends AbstractLoggingClass {

    static private $unique_instance = null;

    static public function get_instance() {
        if (null === self::$unique_instance) {
            self::$unique_instance = new self;
        }
        return self::$unique_instance;
    }

    private $xmlRoot;

    function __construct() {
        parent::__construct();
        $xml_str = file_get_contents(PHEASEL_FILES_CACHE) or die("Could not load files cache");
        $this->xmlRoot = new SimpleXMLElement($xml_str);
    }

    public function get_all_page_urls() {
        $foundNodes = $this->xmlRoot->xpath("pages/item/@url");
        $ret = array();
        foreach($foundNodes as $foundNode) {
            array_push($ret, (string)$foundNode);
        }
        return $ret;
    }

    public function get_all_file_urls() {
        $foundNodes = $this->xmlRoot->xpath("files/item/@url");
        $ret = array();
        foreach($foundNodes as $foundNode) {
            array_push($ret, (string)$foundNode);
        }
        return $ret;
    }

    public function get_all_page_infos() {
        $foundNodes = $this->xmlRoot->xpath("pages/item");
        $ret = array();
        foreach($foundNodes as $foundNode) {
            array_push($ret, $this->get_page_info_from_node($foundNode));
        }
        return $ret;
    }

    /**
     * @param $url string request url
     * @return PageInfo containing info about directory and language of the requested page
     * @throws AmbiguousConfigException if more than one matching page has been found for the provided ID
     */
    public function get_page_info_by_url($url) {
        $this->debug("Retrieving page info for URL $url");
        $foundNodes = $this->xmlRoot->xpath("pages/item[@url='$url']");
        switch(count($foundNodes)) {
            case 0: return null;
            case 1: return $this->get_page_info_from_node($foundNodes[0]);
            default: throw new AmbiguousConfigException(count($foundNodes)." page markup files have been found for URL $url");
        }
    }

    /**
     * @param $url string request url
     * @return PageInfo containing info about directory and language of the requested file
     * @throws AmbiguousConfigException if more than one matching page has been found for the provided ID
     */
    public function get_file_info_by_url($url) {
        $this->debug("Retrieving file info for URL $url");
        $foundNodes = $this->xmlRoot->xpath("files/item[@url='$url']");
        switch(count($foundNodes)) {
            case 0: return null;
            case 1: return $this->get_page_info_from_node($foundNodes[0]);
            default: throw new AmbiguousConfigException(count($foundNodes)." page markup files have been found for URL $url");
        }
    }

    /**
     * @param string $page_id id of needed page
     * @param string $lang (optional) two-character language key, defaults to language key of the current page
     * @return PageInfo PageInfo containing info e.g. about directory and language of the requested page
     * @throws AmbiguousConfigException if more than one matching page has been found for the provided ID
     */
    public function get_page_info($page_id, $lang = null) {
        if($this->debugEnabled()) $this->debug("Retrieving page info for id $page_id and language $lang");
        if($lang == null) $lang = PageInfo::$current->lang;
        $foundNodes = $this->xmlRoot->xpath("pages/item[@id='$page_id' and @lang='$lang']");
        if(count($foundNodes)==0) $foundNodes = $this->xmlRoot->xpath("pages/item[@id='$page_id' and not(@lang)]");
        switch(count($foundNodes)) {
            case 0: return NULL;
            case 1: return $this->get_page_info_from_node($foundNodes[0]);
            default: throw new AmbiguousConfigException(count($foundNodes)." page markup files have been found for id $page_id");
        }
    }

    /**
     * @param string $snippet_id id of needed snippet
     * @param string $lang (optional) two-character language key, defaults to language key of the current page
     * @return SnippetInfo SnippetInfo containing info e.g.  about directory and language of the requested snippet
     * @throws AmbiguousConfigException if more than one matching snippet has been found for the provided ID
     */
    public function get_snippet_info($snippet_id, $lang = null) {
        if($this->debugEnabled()) $this->debug("Retrieving snippet info for id $snippet_id and language $lang");
        if($lang == null) $lang = PageInfo::$current->lang;
        $foundNodes = $this->xmlRoot->xpath("snippets/item[@id='$snippet_id' and @lang='$lang']");
        if(count($foundNodes)==0) $foundNodes = $this->xmlRoot->xpath("snippets/item[@id='$snippet_id' and not(@lang)]");
        switch(count($foundNodes)) {
            case 0: return NULL;
            case 1: return $this->get_snippet_info_from_node($foundNodes[0]);
            default: throw new AmbiguousConfigException(count($foundNodes)." snippet markup files have been found for id $snippet_id");
        }
    }

    /**
     * @param string $tmpl_id id of needed template
     * @param string $lang (optional) two-character language key, defaults to language key of the current page
     * @return TemplateInfo TemplateInfo containing info e.g.  about directory and language of the requested template,
     *      or NULL if non has been found
     * @throws AmbiguousConfigException if more than one matching template has been found for the provided ID
     */
    public function get_template_info($tmpl_id, $lang = null) {
        if($this->debugEnabled()) $this->debug("Retrieving template info for id $tmpl_id and language $lang");
        if($lang == null) $lang = PageInfo::$current->lang;
        $foundNodes = $this->xmlRoot->xpath("templates/item[@id='$tmpl_id' and @lang='$lang']");
        if(count($foundNodes)==0) {
            // template not found, try to find a non-localized variant
            $foundNodes = $this->xmlRoot->xpath("templates/item[@id='$tmpl_id' and not(@lang)]");
        }
        switch(count($foundNodes)) {
            case 0: return NULL;
            case 1: return $this->get_template_info_from_node($foundNodes[0]);
            default: throw new AmbiguousConfigException(count($foundNodes)." template markup files have been found for id $tmpl_id");
        }
    }

    /**
     * @return array of PageInfo objects for each translation of the current page
     */
    public function get_translation_page_infos() {
        $page_id = PageInfo::$current->id;
        $lang = PageInfo::$current->lang;
        $foundNodes = $this->xmlRoot->xpath("pages/item[@id='$page_id' and not(@lang='$lang')]");
        $ret = array();
        foreach($foundNodes as $foundNode) {
            array_push($ret, $this->get_page_info_from_node($foundNode));
        }
        return $ret;
    }

    public function get_xml() {
        return htmlspecialchars((string)$this->xmlRoot->asXML());
    }

    public function get_stats_html() {
        $ret = '<ul>';
        $ret .= $this->get_stats_html_for_type('pages');
        $ret .= $this->get_stats_html_for_type('templates');
        $ret .= $this->get_stats_html_for_type('snippets');
        $ret .= '</ul>';
        return $ret;
    }

    /**
     * @param string $type markup file type: pages|templates|snippets
     * @return string HTML string with some facts about the markup files that have been found
     */
    public function get_stats_html_for_type($type) {
        $num = 0;
        $num_by_lang = array();
        $pageNodes = $this->xmlRoot->xpath("$type/item");
        foreach($pageNodes as $k=>$v) {
            $num++;
            $lang = $v->attributes()->lang;
            $lang = (string)$lang;
            if(!is_null($lang)) {
                if(empty($num_by_lang[$lang])) $num_by_lang[$lang] = 1;
                else $num_by_lang[$lang]++;
            }
        }

        $ret  = "<li>$num $type<ul>";
        foreach($num_by_lang as $k=>$v) {
            $ret.= "<li>$k: $v</li>";
        }
        $ret .= "</ul></li>";

        return $ret;
    }

    /**
     * @param $foundNode
     * @return PageInfo
     */
    public function get_page_info_from_node($foundNode) {
        $ret = new PageInfo();
        $attrs = $foundNode->attributes();
        foreach ($attrs as $key => $value) {
            $value = (string)$value;
            if ($key == 'id') $ret->id = $value;
            else if ($key == 'url') $ret->url = $value;
            else if ($key == 'name') $ret->name = $value;
            else if ($key == 'lang') $ret->lang = $value;
            else if ($key == 'file') $ret->file = $value;
            else if ($key == 'template') $ret->template = $value;
            else $ret->data[$key] = $value;
        }
        return $ret;
    }

    /**
     * @param $foundNode
     * @return TemplateInfo
     */
    public function get_template_info_from_node($foundNode) {
        $ret = new TemplateInfo();
        $attrs = $foundNode->attributes();
        foreach ($attrs as $key => $value) {
            $value = (string)$value;
            if ($key == 'id') $ret->id = $value;
            else if ($key == 'lang') $ret->lang = $value;
            else if ($key == 'file') $ret->file = $value;
            else $ret->data[$key] = $value;
        }
        return $ret;
    }

    /**
     * @param $foundNode
     * @return SnippetInfo
     */
    public function get_snippet_info_from_node($foundNode) {
        $ret = new SnippetInfo();
        $attrs = $foundNode->attributes();
        foreach ($attrs as $key => $value) {
            $value = (string)$value;
            if ($key == 'id') $ret->id = $value;
            else if ($key == 'lang') $ret->lang = $value;
            else if ($key == 'file') $ret->file = $value;
            else $ret->data[$key] = $value;
        }
        return $ret;
    }

}
