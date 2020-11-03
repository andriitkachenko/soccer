<?php

require_once __DIR__ . '/../../../services/db/db_connection.php';

class NgpTable {
    protected $dbConn = null;

    function __construct(DbConnection $dbConnection) {
        if (!$dbConnection) {
            errorLog('NGP table constructor', 'Empty DB connection');
        }
        $this->dbConn = $dbConnection;
    }
}    
?>
