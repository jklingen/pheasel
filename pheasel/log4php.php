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

return array(
    'appenders' =>
    array(
        'myConsoleAppender' =>
        array(
            'class' => 'LoggerAppenderConsole',
            'layout' =>
            array(
                'class' => 'LoggerLayoutPattern',
                'params' =>
                array(
                    'conversionPattern' => '%date{Y-m-d H:i:s} [%p] [%logger:%L] %message %newline',
                ),
            ),
        ),
        'myFileAppender' =>
        array(
            'class' => 'LoggerAppenderRollingFile',
            'layout' =>
            array(
                'class' => 'LoggerLayoutPattern',
                'params' =>
                array(
                    'conversionPattern' => '%date{Y-m-d H:i:s} [%p] [%logger:%L] %message %newline',
                ),
            ),
            'params' =>
            array(
                'file' => PHEASEL_ROOT.'/logs/pheasel.log',
                'maxFileSize' => '1MB',
                'maxBackupIndex' => '10',
            ),
        ),
    ),
    'loggers' =>
    array(),
    'renderers' =>
    array(),
    'rootLogger' =>
    array(
        'level' => 'DEBUG',
        'appenders' =>
        array(
            0 => 'myConsoleAppender',
            1 => 'myFileAppender',
        ),
    ),
);