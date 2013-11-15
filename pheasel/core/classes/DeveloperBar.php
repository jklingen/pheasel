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

class DeveloperBar {

    private $markup_hierarchy;
    private $estimated_page_size;

    function __construct($markup_hierarchy, $estimated_page_size) {
        $this->markup_hierarchy = $markup_hierarchy;
        $this->estimated_page_size = $estimated_page_size;
    }

    public function get_markup() {
        $collapse_attr = get_cookie('pheasel_devbar_collapsed')?'style="display:none;"':'';
        $export_mode = get_cookie('pheasel_devbar_export_php')?'PHP':'HTML';
        return  '
            <link rel="stylesheet" href="'.get_resource_url('/pheasel/core/resources/pheasel-devbar.css').'"/>
            <script src="'.get_resource_url('/pheasel/core/resources/pheasel-devbar.js').'"></script>
            <div id="pheasel-devbar">
                <form id="pheasel-devbar-control" '.$collapse_attr.' method="GET" action="'.get_resource_url('/pheasel/core/pages/export-pages.php').'" target="pheaselexport">
                    <strong><span title="Site directory: '.PHEASEL_PAGES_DIR.'" style="color:#fff;">PHeasel</span> developer bar</strong>
                    &nbsp;|&nbsp;
                    Export as <input type="text" name="mode" value="'.$export_mode.'" onclick="this.value=(this.value==\'PHP\')?\'HTML\':\'PHP\';this.blur();" onfocus="this.blur()";/>:
                    <button type="submit" name="exportall" value="true">all pages</button>
                    <button type="submit" name="exportsingle" value="'.PageInfo::$current->url.'">this page</button>
                    &nbsp;|&nbsp;
                    <button type="button" onclick="popunderExpandCollapse();return false;">Markup</button>
                    size: ~ '.$this->format_bytes($this->estimated_page_size,1).'B

                </form>
                <img class="logo" onclick="devbarExpandCollapse()" src="'.get_resource_url('/pheasel/core/resources/pheasel-logo.png').'"/>
            </div>
            <div id="pheasel-devbar-popunder">
                <div class="filelist">
                <strong>Markup files used in this page</strong>
                <ul>' . $this->markup_hierarchy_list($this->markup_hierarchy) . '</ul>
            </div>

            ';
    }

    private function markup_hierarchy_list($file_node) {
        $ret = "<li>" . substr($file_node->filename, strlen(PHEASEL_PAGES_DIR)) . "</li>";
        if(count($file_node->children) > 0) {
            $ret .= "<ul>";
            foreach($file_node->children as $fn) {
                $ret .= $this->markup_hierarchy_list($fn);
            }
            $ret .= "</ul>";
            return  $ret;
        }
        return $ret;
    }

    private function format_bytes($size, $precision = 2) {
        $base = log($size) / log(1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }


}