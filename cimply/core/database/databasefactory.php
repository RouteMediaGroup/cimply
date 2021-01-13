<?php

namespace Cimply\Core\Database
{
	/**
     * Connect short summary.
     *
     * Connect description.
     *
     * @version 3.0
     * @author Michael Eckebrecht
     */
    use Cimply\Core\Database\Manager;
	class DatabaseFactory extends ConnectService
	{
        private $driver, $manager, $port = null;
        function __construct($connection) {
            $this->driver = $connection['driver'] ?? null;
            parent::__construct($connection);
            try {
                switch ($this->driver)
                {
                    case 'pdo':
                        $this->manager = new Manager\PDO(new \PDO($this->dbtype.':host='.$this->host.';dbname='.$this->dbname, $this->dbuser, $this->dbpass, $this->options ?? null), null) ?? null;
                        break;
                    case 'odbc':
                        $this->manager = new Manager\ODBC(new \PDO("{$this->dbtype}:Driver={SQL Server};Server={$this->host};Database={$this->dbname};Charset={$this->charset}", $this->dbuser, $this->dbpass, $this->options ?? null), null) ?? null;
                        break;
                    case 'sqlsrv':
                        !isset($this->port) ? $this->port = 1434 : null;
                        $this->manager = new Manager\SqlSrv(new \PDO($this->driver.':Server='.$this->host.';Database='.$this->dbname, $this->dbuser, $this->dbpass, $this->options ?? null), null) ?? null;
                        break;
                    case 'odbc2':
                        $this->manager = new Manager\ODBC(new \mysqli("{$this->dbtype}:Driver={SQL Server};Server={$this->host};Database={$this->dbname};Charset={$this->charset}", $this->dbuser, $this->dbpass, $this->options ?? null), null);
                        break;
                    case 'mysqli':
                        $this->manager = new Manager\MySqli(new \mysqli('p:'.$this->host, $this->dbuser, $this->dbpass, $this->dbname), null) ?? null;
                        break;
                    
                    default:
                        $this->manager = null;
                        //throw new \Exception('Es wurde kein Datenbanktreiber definiert.');
                }
            } catch(\Exception $ex) {
                \Debug::VarDump($ex->getMessage());
            }
        }
        function create(): Provider {
            $this->manager = null;
            return (new self($this->connection))->manager;
        }
        function dbm(): ?Provider {
            return $this->manager;
        }
        function close(): void {
            $this->manager = null;
        }
        function __destruct() {
            $this->close();
        }
    }
}