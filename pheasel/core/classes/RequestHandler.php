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
require_once(PHEASEL_CONFIG);
require_once(PHEASEL_CORE . "/includes/util.php");
require_once(PHEASEL_CORE . "/classes/domain/PageInfo.php");
require_once(PHEASEL_CORE . "/classes/AbstractLoggingClass.php");
require_once(PHEASEL_CORE . "/classes/DeveloperBar.php");
require_once(PHEASEL_CORE . "/classes/FileNode.php");
require_once(PHEASEL_CORE . "/classes/SiteConfig.php");
require_once(PHEASEL_CORE . "/classes/SiteConfigWriter.php");
require_once(PHEASEL_CORE . "/includes/page-methods.php");


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
    public $export_mode = false;
    private $rendering = false;
    private $current_page_included = false;

    private $markup_hierarchy; // will hold the hierarchy of all markup files for the current page
    private $markup_stack = array(); // will hold a stack of all markup files currently under processing
    private $head_markup = NULL; // will hold anything from beginning of the document until (exclusive) closing </head>
    private $body_markup = NULL; // will hold anything from (inclusive) opening <body> until end of the document
    private $messages = array();

    /**
     * @param string $relative_url URL relative to the location where pheasel has been extracted to (in case it has not been placed at the server root), ny default determined from the current request
     * @return string HTML or PHP markup of the rendered page; PHP within the markup will be eval'd by default, set $preserve_php to true to avoid that (e.g. to export pages keeping dynamic php functionality)
     * @throws PageNotFoundException if no page could be found for this request
     */
    public function dispatch($relative_url = null) {
        $ret = null;
        try {
            if((PHEASEL_ENVIRONMENT != PHEASEL_ENVIRONMENT_PROD) && PHEASEL_AUTO_UPDATE_FILES_CACHE) {
                SiteConfigWriter::get_instance()->update_cache();
            }
            if(!isset($relative_url)) {
                // pheasel might not be in docroot, discard anything which is above in hierarchy
                $strpos_pheasel = strpos($_SERVER['PHP_SELF'], '/pheasel/');
                $relative_url = substr($_SERVER["REQUEST_URI"], $strpos_pheasel);
            }
            if($this->debugEnabled()) $this->debug("Dispatching request: $relative_url");
            $pageInfo = SiteConfig::get_instance()->get_page_info_by_url($relative_url);

            // no page found? maybe it is not a page, but a file?
            if($pageInfo == null) {
                $pageInfo = SiteConfig::get_instance()->get_file_info_by_url($relative_url);
            }
            // still nothing? extra service: maybe just a trailing slash missing? let's try that:
            if($pageInfo == null && substr($relative_url,-1) != '/') {
                $pageInfo = SiteConfig::get_instance()->get_page_info_by_url($relative_url.'/');
                if($pageInfo != null) {
                    $this->redirect($_SERVER["REQUEST_URI"].'/');
                }
            }
            if($pageInfo == null) {
                // page not found for url
                $pageInfo = $this->handle_page_not_found($relative_url);
            }
        } catch(Exception $ex) {
            // error while trying to find page
            $pageInfo = $this->handle_internal_server_error($ex, $relative_url);
        }

        // page not found should have been handled above. if not, there's something wrong (maybe 404 page was removed)
        if(!isset($pageInfo)) throw new PageNotFoundException("Could not find page for $relative_url");

        try {
            $ret = $this->render_page($pageInfo);
        } catch(Exception $ex) {
            // error while trying to render page
            $pageInfo = $this->handle_internal_server_error($ex, $relative_url);
            $ret = $this->render_page($pageInfo);
        }
        return $ret ;
    }

    public function render_page($pageInfo) {
        PageInfo::$current = $pageInfo;
        $this->collate_markup_for_page($pageInfo);

        if($pageInfo->is_document) {
            $ret = $this->assemble_page_markup();
            // if no one demands otherwise, we output pure HTML
            if(!$this->preserve_php) {
                $ret = $this->eval_markup($ret);
            }
        } else {
            // for other types, just output the markup (or whatever the content is)
            $ret = $this->body_markup;
            $ret = $this->eval_markup($ret);
        }
        return $ret;
    }

    /**
     * Redirect permanently to another URL (HTTP status 301)
     * @param $absolute_url string absolute URL to redirect to (including protocol and domain)
     */
    private function redirect($absolute_url) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $absolute_url");
        exit;
    }

    /**
     * @param $relative_url string URL that could not be found
     * @return PageInfo 404 page that will be displayed
     * @throws PageNotFoundException if not in PROD mode, to give some background info for request
     */
    private function handle_page_not_found($relative_url) {
        header("HTTP/1.1 404 Not Found");
        $pageInfo = SiteConfig::get_instance()->get_page_info('404', PHEASEL_FALLBACK_LANGUAGE);
        if($pageInfo) {
            $pageInfo->url = $relative_url;
        }
        return $pageInfo;
    }

    private function handle_internal_server_error($exception, $relative_url) {
        $this->logger->error("An error occurred while trying to render page $relative_url",$exception);
        header("HTTP/1.1 500 Internal Server Error");
        $lang = isset(PageInfo::$current) ? PageInfo::$current->lang : PHEASEL_FALLBACK_LANGUAGE;
        $pageInfo = SiteConfig::get_instance()->get_page_info('500', PHEASEL_FALLBACK_LANGUAGE);
        if(!$pageInfo && $lang != PHEASEL_FALLBACK_LANGUAGE) $pageInfo = SiteConfig::get_instance()->get_page_info('500', $lang);
        if($pageInfo) {
            $pageInfo->url = $relative_url;
            $pageInfo->data['exception'] = $exception;
        }
        return $pageInfo;
    }

    /**
     * @return string complete HTML document structure assembled from markup files
     */
    private function assemble_page_markup() {
        // for HTML documents: aggregate page markup with head and body + render developer bar
        $ret  = $this->head_markup;
        $ret .= '</head>';
        $ret .= $this->body_markup;
        if(PHEASEL_ENVIRONMENT != PHEASEL_ENVIRONMENT_PROD && !$this->export_mode) {
            $dbar = new DeveloperBar($this->markup_hierarchy, strlen($ret) + 14); // +14 for closing HTML
            $ret .= $dbar->get_markup();
        }
        $ret .= '</body></html>';
        return $ret;
    }

    /**
     * @param $markup string markup that might contain PHP code snippets
     * @return string markup with all PHP code eval'd
     */
    private function eval_markup($markup) {
        ob_start();
        if (eval(' ?>' . $markup . '<?php ') === false) {
            if ($this->errorEnabled()) $this->error("eval of page markup has failed\n---------- non-eval'able code below ----------\n " . $markup . "\n---------- non-eval'able code above ----------");
            throw new Exception("eval of page markup has failed, see pheasel.log for details.");
        }
        $markup = ob_get_clean();
        return $markup;
    }

    private function collate_markup_for_page($page_info) {
        if(!isset($page_info->template)) {
            // no explicit template, check for default template (main)
            $ti = SiteConfig::get_instance()->get_template_info('main');
            if(isset($ti)) $this->read_markup_file(PHEASEL_PAGES_DIR.$ti->file);
            else $this->include_current_page(); // no default template, just go on with the page
        } else {
            $ti = SiteConfig::get_instance()->get_template_info($page_info->template);
            if(isset($ti)) $this->read_markup_file(PHEASEL_PAGES_DIR.$ti->file);
            else throw new TemplateNotFoundException("Template not found for id ".$page_info->template);
        }
    }

    private function include_current_page() {
        /*if($this->current_page_included) {
            throw new Exception("Tried to include the current page more than once.");
        }*/
        $this->current_page_included = true;
        $this->read_markup_file(PHEASEL_PAGES_DIR.PageInfo::$current->file);

    }

    private function include_snippet($id) {
        if($this->debugEnabled()) $this->debug("Including snippet $id");
        $si = SiteConfig::get_instance()->get_snippet_info($id);
        if($si) {
            $this->read_markup_file(PHEASEL_PAGES_DIR . $si->file);
        } else {
            throw new Exception("Snippet not found for id $id");
        }
    }

    private function read_markup_file($file) {
        $this->before_read_markup_file($file);
        if($this->debugEnabled()) $this->debug("Reading markup file $file");
        if(!file_exists($file)) {
            throw new Exception("File not found: $file");
        }
        if(is_dir($file)) {
            throw new Exception("File is a directory: $file");
        }
        $this->hierarchy_include($file);

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
              } else {
                  // TODO what?
              }
        } else {
          $this->append_body(str_replace("<body>","", $parts[0]));
        }
        // TODO exception handling
        $this->after_read_markup_file($file);
    }

    private function before_read_markup_file($file) {
        $this->before_read_file($file);
    }
    private function after_read_markup_file($file) {
        $this->after_read_file($file);
    }
    private function before_read_file($file)
    {
        $fn = new FileNode($file);
        if (count($this->markup_stack) == 0) {
            $this->markup_hierarchy = $fn;
            array_push($this->markup_stack, $fn);
        } else {
            array_push($this->markup_stack[count($this->markup_stack) - 1]->children, $fn);
            array_push($this->markup_stack, $fn);
        }
    }
    private function after_read_file($file) {
        array_pop($this->markup_stack);
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
        $parts = preg_split(PHEASEL_PLACEHOLDER_PREFIX, $markup); // (.*)<ph:(.*)
        $this->append($append_target, $parts[0]);
        for($i=1; $i<count($parts); $i++) {
            $subparts = preg_split(PHEASEL_PLACEHOLDER_SUFFIX, $parts[$i], 2);  // (.*)>(.*)
            $ph = $this->parse_placeholder_string($subparts[0]);
            // TODO iterator implementation goes here
            $this->append($append_target, $this->process_placeholder($ph));
            if(count($subparts )>1) {
                $this->append($append_target, $subparts[1]);
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

    private function process_placeholder_string($placeholder_string) {
        $ph = $this->parse_placeholder_string($placeholder_string);
        return $this->process_placeholder($ph);
    }

    private function parse_placeholder_string($placeholder_string) {
        return new Placeholder($placeholder_string);
    }

    private function process_placeholder($placeholder) {
        $attrs = $placeholder->attributes;
        // TODO extension hook here
        switch($placeholder->name) {
            case 'config': return "";
            case 'msg': return isset($this->messages[$attrs['code']])?$this->messages[$attrs['code']]:NULL;
            case 'resource': return get_resource_url($attrs['url']);
            case 'url': return get_link_url(get_from_array($attrs, 'pageid'), get_from_array($attrs, 'lang'));
            case 'pagename': return get_page_name(isset($attrs['pageid'])?$attrs['pageid']:NULL);
            case 'page': $this->include_current_page(); return "";
            case 'snippet': $this->include_snippet($attrs['id']); return "";
            case 'anchor':
                if(isset(PageInfo::$current->data['anchor.'.$attrs['id']])) {
                    return PageInfo::$current->data['anchor.'.$attrs['id']];
                } else {
                    return $attrs['id'];
                }
            case 'each':
                if(isset($attrs['var'])) return '<?php foreach('.$attrs['var'].' as $it) { ?>';
                else return '<?php } ?>';
        }
        // TODO extension hook here
        // placeholder could not be processed, restore original placeholder - maybe someone else will take care
        return $placeholder->placeholder_string;
    }

    /**
     * Looks for files named e.g. all.inc.php, all.ini, <lang>.inc.php or <lang>.ini along
     * the directory structure and includes/parses them. Same goes for <filename>.inc.php and <filename>.ini (where
     * <filename> is the name of the markup file.
     * @param $file
     */
    private function hierarchy_include($file, $current_dir=PHEASEL_PAGES_DIR) {
        if(!$this->rendering) {
            if($this->debugEnabled()) $this->debug("Looking for files to include in $file");
            $path_info = pathinfo($file);

            // TODO consider iterating from PHEASEL_PAGES_DIR down instead from markup file up
            if($path_info['dirname'] . DIRECTORY_SEPARATOR != PHEASEL_PAGES_DIR) {
                $this->hierarchy_include($path_info['dirname']);
            }

            // e.g. all.inc.php in current directory
            $this->try_include($path_info['dirname'] . DIRECTORY_SEPARATOR . 'all.inc.php');
            // e.g. all.ini in current directory
            $this->find_ini_messages($path_info['dirname'] . DIRECTORY_SEPARATOR . 'all.ini');

            // e.g. en.inc.php in current directory
            $this->try_include($path_info['dirname'] . DIRECTORY_SEPARATOR . PageInfo::$current->lang .  '.inc.php');
            // e.g. en.ini in current directory
            $this->find_ini_messages($path_info['dirname'] . DIRECTORY_SEPARATOR . PageInfo::$current->lang .  '.ini');

            if(isset($path_info['extension'])) { // not for directories or files without extension
                $path_without_extension = $path_info['dirname'] . DIRECTORY_SEPARATOR . $path_info['filename'];

                if(!strpos($path_info['filename'], PageInfo::$current->lang)) {
                    // markup file does not seem to be I18Ned, look out for L10N ini file}

                    // e.g. index.inc.en.php nefore loading index.php
                    $this->try_include($path_without_extension . '.inc.' . PageInfo::$current->lang . '.' . $path_info['extension']);

                    // e.g. index.en.ini nefore loading index.php
                    $this->find_ini_messages($path_without_extension . '.' . PageInfo::$current->lang .  '.ini');
                }

                // e.g. index.en.inc.php nefore loading index.en.php
                $this->try_include($path_without_extension . '.inc.' . $path_info['extension']);
                // e.g. index.en.ini nefore loading index.en.php
                $this->find_ini_messages($path_without_extension . '.ini');
            }
        }
    }

    /**
     * include file if exists, do nothing otherwise
     * @param $file
     */
    function try_include($file) {
        if(is_file($file)) {
            $this->before_read_file($file);
            include $file;
            $this->after_read_file($file);
        }
    }

    /**
     * parse ini file if exists, do nothing otherwise
     * @param $file
     * @return array|bool
     */
    function try_parse_ini($file) {
        $ret = false;
        if(is_file($file)) {
            $this->before_read_file($file);
            $ret =  parse_ini_file($file, true);
            $this->after_read_file($file);
        }
        return $ret;

    }

    /**
     * parse ini file if exists, do nothing otherwise
     * @param $file
     */
    private function find_ini_messages($file) {
        $parsed = $this->try_parse_ini($file);

        if($parsed && count($parsed) > 0) {
            if(isset($parsed['messages']) && count($parsed['messages']) > 0) $this->messages = array_merge($this->messages, $parsed['messages']);
        }
    }

    public function discard() {
        self::$unique_instance = null;
    }

}
