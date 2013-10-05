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

/*
${config
url = /sitemap.xml
}$
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
