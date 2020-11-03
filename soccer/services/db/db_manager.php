<?php

require_once __DIR__ . '/db_connection.php';

class DbManager {
    private $dbConn = null;

    function __construct(DbConnection $dbConnection) {
        if (!$dbConnection) {
            errorLog("DB manager constructor", "Empty DB connection");
        }
        $this->dbConn = $dbConnection;
    }
}

?>

