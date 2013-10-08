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

require_once("../../../pheasel-config.php");
require_once("../../globals.php");
require_once('../classes/SiteConfigWriter.php');

SiteConfigWriter::get_instance()->update_cache();
?>
<h1>Page cache updated</h1>
<?php echo SiteConfig::get_instance()->get_stats_html(); ?>
<h2>Generated XML</h2>
<pre>
<?php echo SiteConfig::get_instance()->get_xml(); ?>
</pre>
