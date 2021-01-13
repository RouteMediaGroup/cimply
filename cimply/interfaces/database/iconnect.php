<?php

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