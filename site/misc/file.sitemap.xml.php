<?php

/*
<ph:config
    url = /sitemap.xml
/>
 */

/**
 * Dynamically generated XML sitemap, feel free to change it to fit your needs, e.g. add priority or leave out some pages.
 * See http://www.sitemaps.org/ for more information on XML sitemaps.
 */
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php
        foreach(SiteConfig::get_instance()->get_all_page_infos() as $pi) {
            if(isset($pi->url)) {
    ?>

    <url>
        <loc><?php echo $pi->url; ?></loc>
        <lastmod><?=date('Y-m-d',filemtime(PHEASEL_PAGES_DIR.$pi->file))?></lastmod>
    </url>
    <?php
            }
        }
    ?>

</urlset>
