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

require_once "PheaselTestCase.php";
require_once "../pheasel/classes/SiteConfigWriter.php";
require_once "../pheasel/classes/error/MarkupNotFoundException.php";

class SiteConfigTest extends PheaselTestCase {

    protected function setUp() {
        // generate the XML
        SiteConfigWriter::get_instance()->update_cache();
    }

    public function testGetPageWithIdAndLangInFilename() {
        $pi = SiteConfig::get_instance()->get_page_info("testdir.id-and-lang-in-filename", "en");
        $this->assertEquals($pi->lang, "en");
    }

    public function testGetPageWithIdInFilename() {
        $pi = SiteConfig::get_instance()->get_page_info("testdir.id-in-filename", "de");
        $this->assertEquals($pi->lang, "de");
    }

    public function testGetPageWithIdInComment() {
        $pi = SiteConfig::get_instance()->get_page_info("id-in-comment", "de");
        $this->assertEquals($pi->lang, "de");
    }

    public function testGetPageWithCurrentLanguage() {
        $pi1 = SiteConfig::get_instance()->get_page_info("testdir.id-and-lang-in-filename", "en");
        PageInfo::$current = $pi1;

        $pi2 = SiteConfig::get_instance()->get_page_info("id-in-comment");
        $this->assertEquals($pi2->lang, "en");
    }

    public function testGetPagesWithIniConfig() {
        $pi = SiteConfig::get_instance()->get_page_info("conf-in-ini", "de");
        $this->assertEquals($pi->lang,"de");
        $this->assertEquals($pi->url,"/konfig-in-ini-datei/");

        $pi = SiteConfig::get_instance()->get_page_info("conf-in-ini", "en");
        $this->assertEquals($pi->lang,"en");
        $this->assertEquals($pi->url,"/config-in-ini-file/");

    }

    public function testGetTranslations() {
        $pi1 = SiteConfig::get_instance()->get_page_info("id-in-comment","de");
        PageInfo::$current = $pi1;

        $translations = SiteConfig::get_instance()->get_translation_page_infos();
        $en_found = false;
        foreach($translations as $translation) {
            $this->assertNotEquals($translation->lang, "de");
            $this->assertEquals($translation->id, "id-in-comment");
            if($translation->lang == "en") $en_found = true;
        }
        $this->assertTrue($en_found);
    }

    public function testPageNotFound() {
        $pi = SiteConfig::get_instance()->get_page_info("non-existent-id");
        $this->assertNull($pi);
    }


}