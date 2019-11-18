<?php

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/../config.php';

function saveLastError($conn) {
    appendError(json_encode($conn->errorInfo()));
}

function makeConnection() {
    return openDbConnection(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
}

function openDbConnection($server, $dbname, $username, $password) {
    global $lastError;

    $conn = null;
    try {
        $conn = new PDO("mysql:host=$server;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        $lastError = "DB connection failed: " . $e->getMessage();
    }
    return $conn;
}
function closeDbConnection($conn) {
    $conn = null;
}

function exec_query($conn, $query) {
    global $lastError;
    if (empty($conn)) {
        $lastError = "DB connection not set for query " . $query;
        return false;
    }
    $res = $conn->exec($query);
    if ($res == false && $res !== 0) {
        saveLastError($conn);
        return false;
    }
    return true; 
}

?>