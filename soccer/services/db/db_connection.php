<?php

require_once __DIR__ . '/db_settings.php';

interface iDbConnection {
    public function close() : void;
    public function get() : PDO;
    public function exec($query);
    public function getLastError() : string;
}

class DbConnection implements iDbConnection {
    private $connection = null;
    private $lastError = null;

    public function __construct (DbSettings $settings) {
        try {
            $server = $settings->getServer();
            $dbname = $settings->getName();
            $conn = new PDO("mysql:host=$server;dbname=$dbname", $settings->getUser(), $settings->getPassword());
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection = $conn;
        } catch(PDOException $e) {
            $this->lastError = $e->getMessage();
        }
    }

    public function __destruct() {
        $this->close();
    }

    public function close() : void {
        $this->connection = null;
    }

    public function get() : PDO {
        return $this->connection;
    }

    public function getLastError() : string {
        return $this->lastError;
    }

    public function exec($query) {
        if (empty($this->connection)) {
            $this->lastError = "DB connection not set";
            return false;
        }
        $res = false;
        try {
            $res = $this->connection->exec($query);
        } catch(PDOException $e) {
            $this->lastError = "SQL query failed: " . $e->getMessage();
            return false;
        }
        if ($res == false && $res !== 0) {
            $this->lastError = json_encode($this->connection->errorInfo());
        }
        return $res;
    }    
}

?>