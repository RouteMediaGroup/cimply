<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Interfaces\Database
{
	/**
     * Description of IConnect
     *
     *
     * @author Michael Eckebrecht
     */
	interface IConnect {
        public function setHost(?string $hostName);
        public function setDbName(string $dbName);
        public function setDbType(string $dbType);
        public function setUserName(string $userName);
        public function setPassword(?string $password);
        public function setCharset(?string $charset);
        public function setOptions(?array $options);
    }
}