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

$last_link_target_page_info = null;

function page_here() {
    RequestHandler::get_instance()->include_current_page();
}

function snippet_here($snippet_id) {
    $snippet_info = SiteConfig::get_instance()->get_snippet_info($snippet_id);
    RequestHandler::get_instance()->read_markup_file(PHEASEL_PAGES_DIR.$snippet_info->file);
    //include PHEASEL_PAGES_DIR.$snippet_info->file;
}

function link_url_here($page_id) {
    global $last_link_target_page_info;
    $last_link_target_page_info = SiteConfig::get_instance()->get_page_info($page_id);
    $target_uri = $last_link_target_page_info->uri;
    $current_uri = PageInfo::$current->uri;
    echo get_relative_uri($current_uri, $target_uri);
}

// TODO we should move this somewhere else, it is used by RequestHandler
function get_link_url($page_id) {
    global $last_link_target_page_info;
    $anchor = NULL;
    if(strpos($page_id,"#") !== false) {
        $hs = explode("#", $page_id);
        $page_id = $hs[0];
        if(strlen($page_id)==0) $page_id = PageInfo::$current->id;
        $anchor = $hs[1];
    }
    if(strlen($page_id)==0) $page_id = PageInfo::$current->id;
    $last_link_target_page_info = SiteConfig::get_instance()->get_page_info($page_id);
    $target_uri = $last_link_target_page_info->uri;
    $current_uri = PageInfo::$current->uri;
    if(isset($last_link_target_page_info->data["anchor.".$anchor])) {
        $anchor = '#'.$last_link_target_page_info->data["anchor.".$anchor];
    }
    return get_relative_uri($current_uri, $target_uri).$anchor;
}

function link_text_here() {
    global $last_link_target_page_info ;
    echo $last_link_target_page_info->name;
}
function get_page_name($page_id = NULL) {
    if($page_id == NULL) {
        global $last_link_target_page_info ;
        return $last_link_target_page_info->name;
    } else {
        $pi = SiteConfig::get_instance()->get_page_info($page_id);
        return $pi->name;
    }
}



function resource_url_here($resource_name) {
    $current_uri = PageInfo::$current->uri;
    //echo $current_uri.'+++'.$resource_name;
    echo get_relative_uri($current_uri, $resource_name);
}

function get_resource_url($resource_name) {
    $current_uri = PageInfo::$current->uri;
    //echo $current_uri.'+++'.$resource_name;
    return get_relative_uri($current_uri, $resource_name);
}

/**
 * Build a relative href from
 * @param $current_uri string URI of the current document
 * @param $target_uri string URI of the document to link to
  */
function get_relative_uri($current_uri, $target_uri)
{
    $target_exp = explode("/", $target_uri);
    $current_exp = explode("/", $current_uri);
    while ( count($target_exp)>0 && $target_exp[0] == $current_exp[0]) {
        array_shift($target_exp);
        array_shift($current_exp);
    }
    for($i=0; $i< count($current_exp)-1; $i++) {
        array_unshift($target_exp, "..");
    }
    return implode("/", $target_exp);

}