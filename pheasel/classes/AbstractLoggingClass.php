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

require_once(PHEASEL_ROOT."/lib/log4php/Logger.php");

abstract class AbstractLoggingClass {

    protected $logger;

    function __construct()
    {

        Logger::configure(PHEASEL_ROOT.'/log4php.php');
        $this->logger = Logger::getLogger(get_class($this));
    }

    protected function trace($message) {
        if($this->logger->isTraceEnabled())  $this->logger->trace($message);
    }

    protected function debug($message) {
        if($this->logger->isDebugEnabled())  $this->logger->debug($message);
    }

    protected function info($message) {
        if($this->logger->isInfoEnabled()) $this->logger->info($message);
    }

    protected function warn($message) {
        if($this->logger->isWarnEnabled()) $this->logger->warn($message);
    }

    protected function error($message) {
        if($this->logger->isErrorEnabled()) $this->logger->error($message);
    }


}