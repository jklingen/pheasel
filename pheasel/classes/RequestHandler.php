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

require_once(PHEASEL_ROOT . "/../pheasel_config.php");
require_once(PHEASEL_ROOT . "/includes/util.php");
require_once(PHEASEL_ROOT . "/classes/domain/PageInfo.php");
require_once(PHEASEL_ROOT . "/classes/AbstractLoggingClass.php");
require_once(PHEASEL_ROOT . "/classes/SiteConfig.php");
require_once(PHEASEL_ROOT . "/classes/SiteConfigWriter.php");
require_once(PHEASEL_ROOT . "/includes/page-methods.php");


class RequestHandler extends AbstractLoggingClass {


    static private $unique_instance = null;

    static public function get_instance() {
        if (null === self::$unique_instance) {
            self::$unique_instance = new self;
        }
        return self::$unique_instance;
    }

    static private $APPEND_TARGET_HEAD = 1;
    static private $APPEND_TARGET_BODY = 2;


    public $preserve_php = false;
    public $batch_mode = false;
    private $rendering = false;
    private $current_page_included = false;

    private $head_markup = NULL; // will hold anything from beginning of the document until (exclusive) closing </head>
    private $body_markup = NULL; // will hold anything from (inclusive) opening <body> until end of the document
    private $messages = array();

    /**
     * @param string $relative_uri URI relative to the location where pheasel has been extracted to (in case it has not been placed at the server root), ny default determined from the current request
     * @return string HTML or PHP markup of the rendered page; PHP within the markup will be eval'd by default, set $preserve_php to true to avoid that (e.g. to export pages keeping dynamic php functionality)
     */
    public function render_page($relative_uri = NULL) {
        if(PHEASEL_AUTO_UPDATE_FILES_CACHE) {
            SiteConfigWriter::get_instance()->update_cache();
        }
        if(!isset($relative_uri)) {
            // pheasel might not be in docroot, discard anything which is above in hierarchy
            $strpos_pheasel = strpos($_SERVER['PHP_SELF'], '/pheasel/');
            $relative_uri = substr($_SERVER["REQUEST_URI"], $strpos_pheasel);
        }
        $pageInfo = SiteConfig::get_instance()->get_page_info_by_uri($relative_uri);
        PageInfo::$current = $pageInfo;
        if($pageInfo->template != null) {
            $this->include_current_template();
        } else {
            $this->include_current_page();
        }
        $ret  = $this->head_markup;
        $ret .= '</head>';
        $ret .= $this->body_markup;
        $ret .= '</body></html>';
        // if no one demands otherwise, we output pure HTML
        if(!$this->preserve_php) $ret = eval("?>$ret");
        return $ret;
    }

    public function include_current_template() {
        $ti = SiteConfig::get_instance()->get_template_info(PageInfo::$current->template);
        $this->read_markup_file(PHEASEL_PAGES_DIR.$ti->file);
    }

    public function include_current_page() {
        if($this->current_page_included && !$this->batch_mode) {
            throw new Exception("Tried to include the current page more than once.");
        }
        $this->current_page_included = true;
        $this->read_markup_file(PHEASEL_PAGES_DIR.PageInfo::$current->file);

    }

    public function include_snippet($id) {
        $this->read_markup_file(PHEASEL_PAGES_DIR.SiteConfig::get_instance()->get_snippet_info($id)->file);
    }

  public function read_markup_file($file) {
      $this->hierarchy_include($file);
      // TODO maybe make sure that everything is included *before* anything is rendered, so that a snippet can e.g. provide stuff for the template, too? not sure whether this is neccessary, though
      $markup = file_get_contents($file);
      $parts = explode('<head>',$markup); // (.*)<head>(.*)
      if(count($parts)>1) {
          if(!isset( $this->head_markup)) {
              // init header section
              $this->append_head($parts[0] . '<head>');
          }
          $parts = explode('</head>', $parts[1]); // <head>(.*)</head>(.*)
          // append to existing header section
          $this->append_head($parts[0]);

          $parts = explode('</body>', $parts[1]); // </head>(.*)</body>(.*)
          if(count($parts)>1) {
              if(!isset($this->body_markup)) {
                  // init body section
                  $this->append_body($parts[0]);
              } else {
                  // append to body section (removing starting <body> tag before)
                  $this->append_body(str_replace("<body>","", $parts[0]));
              }
          }
      }else {
          $this->append_body(str_replace("<body>","", $parts[0]));
      }
      // TODO exception handling
    }

    private function append_head($markup) {
        if(!isset( $this->head_markup)) {
            $this->head_markup = "";
        }
        $this->process_and_append_markup(self::$APPEND_TARGET_HEAD, $markup);
    }

    private function append_body($markup) {
        if(!isset( $this->body_markup)) {
            $this->body_markup = "";
        }
        $this->process_and_append_markup(self::$APPEND_TARGET_BODY, $markup);
    }

    // searches for pheasel_placeholders
    private function process_and_append_markup($append_target, $markup) {
        $parts = explode(PLACEHOLDER_PREFIX, $markup); // (.*)${(.*)
        $this->append($append_target, $parts[0]);
        for($i=1; $i<count($parts); $i++) {
            $subparts = explode(PLACEHOLDER_SUFFIX, $parts[$i]);  // (.*)}$(.*)
            $this->append($append_target, $this->process_placeholder_string($subparts[0]));
            if(count($subparts )>1) {
                $this->append($append_target, $this->unescape_escaped_placeholders($subparts[1]));
            }
        }
    }

    private function append($appendTarget, $markup) {
        switch($appendTarget) {
            case self::$APPEND_TARGET_HEAD:
                $this->head_markup .= $markup;
                break;
            case self::$APPEND_TARGET_BODY:
                $this->body_markup .= $markup;
                break;
        }
    }

    private function process_placeholder_string($placeholder) {
        $ph = new Placeholder($placeholder);
        $ret = $this->process_placeholder($ph);
        if($ret === NULL) $ret = PLACEHOLDER_PREFIX .$placeholder. PLACEHOLDER_SUFFIX; // if we cannot handle it, restore original placeholder - maybe someone else will take care
        return $ret;
    }

    private function process_placeholder($placeholder) {
        $attrs = $placeholder->attributes;
        // TODO extension hook here
        switch($placeholder->name) {
            case 'config': return "";
            case 'msg': return isset($this->messages[$attrs['code']])?$this->messages[$attrs['code']]:NULL;
            case 'resource': return get_resource_url($attrs['uri']);
            case 'url': return get_link_url($attrs['pageid']);
            case 'pagename': return get_page_name(isset($attrs['pageid'])?$attrs['pageid']:NULL);
            case 'page': $this->include_current_page(); return "";
            case 'snippet': $this->include_snippet($attrs['id']); return "";
            case 'anchor':
                if(isset(PageInfo::$current->data['anchor.'.$attrs['id']])) {
                    return PageInfo::$current->data['anchor.'.$attrs['id']];
                } else {
                    return $attrs['id'];
                }
            // TODO implement placeholders
            default: return NULL;
        }
        // TODO extension hook here
    }

    private function unescape_escaped_placeholders($markup_with_escaped_placeholders) {
        $ret = str_replace(PLACEHOLDER_PREFIX_ESCAPED, PLACEHOLDER_PREFIX, $markup_with_escaped_placeholders);
        $ret = str_replace(PLACEHOLDER_SUFFIX_ESCAPED, PLACEHOLDER_SUFFIX, $ret);
        return $ret;
    }

    private function hierarchy_include($file) {
        $path_info = pathinfo($file);

        if(!$this->rendering) {
            // TODO check whether this is safe in other environments
            if($path_info['dirname'] . DIRECTORY_SEPARATOR != PHEASEL_PAGES_DIR) {
                $this->hierarchy_include($path_info['dirname']);
            }

            // e.g. all.inc.php in current directory
            $this->try_include($path_info['dirname'] . DIRECTORY_SEPARATOR . 'all.inc.php');
            // e.g. all.ini in current directory
            $this->try_parse_ini($path_info['dirname'] . DIRECTORY_SEPARATOR . 'all.ini');

            // e.g. en.inc.php in current directory
            $this->try_include($path_info['dirname'] . DIRECTORY_SEPARATOR . PageInfo::$current->lang .  '.inc.php');
            // e.g. en.ini in current directory
            $this->try_parse_ini($path_info['dirname'] . DIRECTORY_SEPARATOR . PageInfo::$current->lang .  '.ini');

            if(isset($path_info['extension'])) { // not for directories or files withcout extension
                // e.g. index.en.inc.php nefore loading index.en.php
                $this->try_include($path_info['dirname'] . DIRECTORY_SEPARATOR .  $path_info['filename'] . '.inc.' . $path_info['extension']);
                // e.g. index.en.ini nefore loading index.en.php
                $this->try_parse_ini($path_info['dirname'] . DIRECTORY_SEPARATOR .  $path_info['filename'] . '.ini');
            }
        }
    }

    /**
     * include file if exists
     * @param $file
     */
    public function try_include($file) {
        if(is_file($file)) include $file;
    }

    public function try_parse_ini($file) {
        $parsed = false;
        if(is_file($file)) $parsed = parse_ini_file($file);

        if($parsed && count($parsed) > 0) {
            $this->messages = array_merge($this->messages, $parsed);
        }
    }


}
