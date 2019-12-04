<?php

namespace Cimply\Core\Database
{
	/**
	 * Connect short summary.
	 *
	 * Connect description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */

    use Cimply\Interfaces\Database\IConnect;
	class ConnectService implements IConnect
	{
        public $connection;
        protected $dbtype, $host, $dbname, $dbuser, $dbpass, $charset, $options;
        function __construct($con) {
            $this->setConnection($con);   
        }

        protected function setConnection($con) {
            $this->connection = $con;
			if(!isset($con['type'])) {
				return;
			}
			$this->setDbType($con['type']);
			$this->setHost($con['host']);
			$this->setDbName($con['name']);
			$this->setUserName($con['user']);
			$this->setPassword($con['pass']);
            $this->setCharset($con['charset'] ?? null);
			$this->setOptions($con['options'] ?? null);				
        }

        #region Cimply\Interfaces\IConnect Members

        /**
         *
         * @param string $hostName
         */
        function setHost(?string $hostName)
        {
            $this->host = $hostName;
        }

        /**
         *
         * @param string $dbName
         */
        function setDbName(string $dbName)
        {
            $this->dbname = $dbName;
        }

        /**
         *
         * @param string $dbType
         */
        function setDbType(string $dbType)
        {
            $this->dbtype = $dbType;
        }

        /**
         *
         * @param string $userName
         */
        function setUserName(string $userName)
        {
            $this->dbuser = $userName;
        }

        /**
         *
         * @param string $password
         */
        function setPassword(?string $password)
        {
            $this->dbpass = $password;
        }
        
        /**
         *
         * @param string $options
         */
        function setCharset(?string $charset)
        {
            $this->charset = $charset ?? null;
        }
        
        /**
         *
         * @param string $options
         */
        function setOptions(?array $options)
        {
            $this->options = $options ?? null;
        }

        #endregion
    }
}