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
require_once "../core/classes/domain/Placeholder.php";

class PlaceholderTest extends PheaselTestCase {

    protected function setUp() {

    }

    public function testParseMultiLine() {
        $p = new Placeholder("multiline\nkey1=value1\nkey2=value2");
        $this->assertEquals($p->name, "multiline");
        $this->assertEquals($p->attributes["key1"], "value1");
        $this->assertEquals($p->attributes["key2"], "value2");
    }

    public function testParseSingleLine() {
        $p = new Placeholder("multiline key1=value1 key2=value2");
        $this->assertEquals($p->name, "multiline");
        $this->assertEquals($p->attributes["key1"], "value1");
        $this->assertEquals($p->attributes["key2"], "value2");
    }

    public function testParseSingleLineWithSpaces() {
        $p = new Placeholder("multiline key1=value 1 key2=value 2");
        $this->assertEquals($p->name, "multiline");
        $this->assertEquals($p->attributes["key1"], "value 1");
        $this->assertEquals($p->attributes["key2"], "value 2");
    }

    public function testParseIgnoreSections() {
        $p = new Placeholder("multiline\n[keysection]\nkey1=value1\nkey2=value2");
        $this->assertEquals($p->name, "multiline");
        $this->assertEquals($p->attributes["key1"], "value1");
        $this->assertEquals($p->attributes["key2"], "value2");
    }

    public function testParseSections() {
        $p = new Placeholder("multiline\nnosectionkey=nosectionvalue\n[keysection]\nkey1=value1\nkey2=value2", Placeholder::SECTION_MODE_PROCESS);
        $this->assertEquals($p->name, "multiline");
        $this->assertEquals($p->attributes["keysection"]["key1"], "value1");
        $this->assertEquals($p->attributes["keysection"]["key2"], "value2");
        $this->assertEquals($p->attributes["nosectionkey"], "nosectionvalue");
    }



}