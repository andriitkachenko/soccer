<?php

require_once __DIR__ . '/db_settings.php';
require_once __DIR__ . '/../../php/logs.php';

interface iDbConnection {
    public function close() : void;
    public function exec($query);
    public function query($query);
    public function getLastError();
    public function connected() : bool;
}

class DbConnection implements iDbConnection {
    private $pdo = null;
    private $lastError = null;
    private $settings = null;

    public function __construct(DbSettings $settings) {
        $this->settings = $settings;
        $this->reconnect();
    }

    private function reconnect() {
        if (empty($this->settings)) return null;

        $settings = $this->settings;
        try {
            $server = $settings->getServer();
            $dbname = $settings->getName();
            $this->close();
            $pdo = new PDO("mysql:host=$server;dbname=$dbname", $settings->getUser(), $settings->getPassword());
            // set the PDO error mode to exception
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo = $pdo;
        } catch(PDOException $e) {
            errorLog("DBConnection constructor", $e->getMessage());
            $this->lastError = $e->getMessage();
            $this->pdo = null;
        }
        return !is_null($this->pdo);
    }

    public function __destruct() {
        $this->close();
    }

    public function close() : void {
        $this->pdo = null;
    }
    
    public function connected() : bool {
        if (!$this->pdo) return false;
        try {
            $this->pdo->query("SELECT 1+1");
        } catch (PDOException $e) {
            return false;
        }        
        return true;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function exec($query) {
        if (!$this->checkConnection()) {
            return false;
        }
        $pdo = $this->pdo;
        if (empty($pdo)) {
            errorLog('DBConnection exec', ["DB connection not set", $query]);
            $this->lastError = "DB connection not set";
            return false;
        }
        try {
            $res = $pdo->exec($query);
        } catch(PDOException $e) {
            $errorInfo =  ["SQL query failed", $e->getMessage(), $query];
            errorLog('DBConnection exec', $errorInfo);
            $this->lastError = implode(PHP_EOL, $errorInfo);
            return false;
        }
        $ok = $res != false || $res === 0;
        if (!$ok) {
            $errorInfo = json_encode($pdo->errorInfo());
            errorLog('DBConnection exec', $errorInfo);
            $this->lastError = $errorInfo;            
        }
        return $ok;
    }    

    public function query($query) {
        if(!$this->checkConnection()) {
            return false;
        }        
        $pdo = $this->pdo;        
        if (empty($pdo)) {
            errorLog('DBConnection query', ["DB connection not set", $query]);
            $this->lastError = "DB connection not set";
            return false;
        }
        try {
            $res = $pdo->query($query);
        } catch(PDOException $e) {
            $errorInfo =  ["SQL query failed", $e->getMessage(), $query];
            errorLog('DBConnection query', $errorInfo);
            $this->lastError = implode(PHP_EOL, $errorInfo);
            return false;            
        }
        if ($res === false) {
            $errorInfo = json_encode($pdo->errorInfo());
            errorLog('DBConnection query', $errorInfo);
            $this->lastError = $errorInfo;
        }
        return $res;
    }  

    private function checkConnection() {
        return $this->connected() || $this->reconnect();
    }
}

?>