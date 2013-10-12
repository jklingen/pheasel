<?php
/**
 * Custom 404 page, feel free to change it to fit your needs.
 * Note: won't be displayed when PHeasel is running in development environment, because the error page
 * contains more background information.
 */

/*
${config
    id=   404
    name= Page not found
}$
 */
?>
<html>
<head>

    <title>Page not found</title>
    <meta name="robots" content="noindex,follow"/>
</head>
<body>
<h1>Oooops - Page not found :(</h1>
<p>Sorry, we could not find the page you are looking for.</p>
<?php
    // try to find best matching URL for suggestion
    $url = PageInfo::$current->url;
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
?>
</body>
</html>