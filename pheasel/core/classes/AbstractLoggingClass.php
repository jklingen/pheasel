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

require_once(PHEASEL_ROOT . "/pheasel/lib/log4php/Logger.php");

abstract class AbstractLoggingClass {

    protected $logger;

    function __construct()
    {

        Logger::configure(PHEASEL_ROOT.'/pheasel/log4php.php');
        $this->logger = Logger::getLogger(get_class($this));
    }

    protected function trace($message) {
        if($this->traceEnabled())  $this->logger->trace($message);
    }

    protected function debug($message) {
        if($this->debugEnabled())  $this->logger->debug($message);
    }

    protected function info($message) {
        if($this->infoEnabled()) $this->logger->info($message);
    }

    protected function warn($message) {
        if($this->warnEnabled()) $this->logger->warn($message);
    }

    protected function error($message) {
        if($this->errorEnabled()) $this->logger->error($message);
    }

    protected function traceEnabled() {
        return $this->logger->isTraceEnabled();
    }

    protected function debugEnabled() {
        return $this->logger->isDebugEnabled();
    }

    protected function infoEnabled() {
        return $this->logger->isInfoEnabled();
    }

    protected function warnEnabled() {
        return $this->logger->isWarnEnabled();
    }

    protected function errorEnabled() {
        return $this->logger->isErrorEnabled();
    }


}