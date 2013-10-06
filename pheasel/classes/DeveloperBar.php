<?php
/**
 * Created by IntelliJ IDEA.
 * User: jens
 * Date: 06.10.13
 * Time: 17:22
 * To change this template use File | Settings | File Templates.
 */

class DeveloperBar {

    private $estimated_page_Size;

    function __construct($estimated_page_Size) {
        $this->estimated_page_Size = $estimated_page_Size;
    }

    public function get_markup() {
        $collapse_attr = get_cookie('pheasel_devbar_collapsed')?'style="display:none;"':'';
        $export_mode = get_cookie('pheasel_devbar_export_php')?'PHP':'HTML';
        return  '
            <link rel="stylesheet" href="'.get_resource_url('/pheasel/resources/pheasel-devbar.css').'"/>
            <script src="'.get_resource_url('/pheasel/resources/pheasel-devbar.js').'"></script>
            <div id="pheasel-devbar">
                <form id="pheasel-devbar-control" '.$collapse_attr.' method="GET" action="'.get_resource_url('/pheasel/export-pages.php').'" target="pheaselexport">
                    <strong><span title="Site directory: '.PHEASEL_PAGES_DIR.'" style="color:#fff;">PHeasel</span> developer bar</strong>
                    &nbsp;|&nbsp;
                    Export as <input type="text" name="mode" value="'.$export_mode.'" onclick="this.value=(this.value==\'PHP\')?\'HTML\':\'PHP\';this.blur();" onfocus="this.blur()";/>:
                    <button type="submit" name="exportall" value="true">all pages</button>
                    <button type="submit" name="exportsingle" value="'.PageInfo::$current->url.'">this page</button>
                    &nbsp;|&nbsp;
                    Markup size: ~ '.$this->format_bytes($this->estimated_page_Size,1).'B
                </form>
                <img class="logo" onclick="devbarExpandCollapse()" src="'.get_resource_url('/pheasel/resources/pheasel-logo.png').'"/>
            </div>';
    }

    private function format_bytes($size, $precision = 2) {
        $base = log($size) / log(1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }


}