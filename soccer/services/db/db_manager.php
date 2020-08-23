<?php

require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/../../logs.php';
require_once __DIR__ . '/../../log/log.php';
require_once __DIR__ . '/../../utils.php';


interface iDbManager {
    public function insertLog(Log $log);
    public function insertStats($stats);
    public function updateGames($games);
}

abstract class DbManager implements iDbManager {
    private $dbConn = null;
    private $lastError = null;

    function __construct(PDO $dbConnection) {
        $this->dbConn = $dbConnection;
    }
}

?>

