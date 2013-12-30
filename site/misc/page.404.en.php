<?php
/**
 * Custom 404 page, feel free to change it to fit your needs.
 *
 * Note (1): shows some helpful additional information in DEV mode.
 *
 * Note (2): When exporting as HTML or PHP, this page will be exported e.g. to 404.html or 404.php, respectively.
 * But that does not mean that it will automatically be used when a page is not found.
 *
 * If your web server has mod_rewrite enabled you need to add an .htaccess file specifying
 * ErrorDocument 404 /404.html
 *
 * If it does not, please get in touch with your hoster, there are probably has file name conventions for 404 pages,
 * e.g. that they must be called 404.html.
 *
 * Note (3): when used as described in (2), you might see broken links or references to javascript/stylesheets on
 * this page. If this is the case, please edit pheasel-config.php and insert a value for PHEASEL_CONTEXT_PATH, so
 * that the RequestHandler can insert references relative to the webserver root.
 */
?>
<!DOCTYPE html>
<html>
<head>
	<ph:config id="404" name="Page not found" />
    <title>Page not found</title>
    <meta name="robots" content="noindex,follow"/>
</head>
<body>
<h1>Oooops - Page not found :(</h1>
<p>Sorry, we could not find the page you are looking for.</p>
<?php
    // try to find best matching URL for suggestion
    $url = PageInfo::$current->url;
    if(isset($url)) {
        $pis = SiteConfig::get_instance()->get_all_page_infos();
        $best_match_pi = null;
        $best_match_distance = 999999;
        foreach($pis as $pi) {
            $dist = levenshtein($url, $pi->url);
            if($dist < $best_match_distance) {
                $best_match_distance = $dist;
                $best_match_pi = $pi;
            }
        }
        if($best_match_pi) {
            $best_match_url = get_relative_uri($url, $best_match_pi->url);
            echo 'Did you mean to visit <a href="' . $best_match_url .'">' .$best_match_pi->name . '</a>?';
        }

        // list all available pages
        if(PHEASEL_ENVIRONMENT==PHEASEL_ENVIRONMENT_DEV) {
            echo "<p>Requested URL was: <strong>$url</strong></p>";
            echo "<p>Available URLs are:</p><ul>";
            foreach(SiteConfig::get_instance()->get_all_page_infos() as $pi) {
                if($pi->url) {
                    echo "<li><strong>$pi->url</strong> (id: $pi->id)</li>";
                }
            }
            echo "</ul>";
        }
    }
?>
</body>
</html>