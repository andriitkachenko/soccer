<?php

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/../config.php';

function getLastError($conn) {
    return json_encode($conn->errorInfo());
}

function saveLastError($conn) {
    appendError(getLastError($conn));
}

function makeDbConnection($local) {
    return $local 
        ? openDbConnection(DB_SERVER_DEV, DB_NAME_DEV, DB_USER_DEV, DB_PASSWORD_DEV)
        : openDbConnection(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
}

function openDbConnection($server, $dbname, $username, $password) {
    $conn = null;
    try {
        $conn = new PDO("mysql:host=$server;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        updateDbErrorLog("DB connection failed: " . $e->getMessage());
    }
    return $conn;
}
function closeDbConnection($conn) {
    $conn = null;
}

function exec_query($conn, $query) {
    if (empty($conn)) {
        updateDbErrorLog("DB connection not set", $query);
        return false;
    }
    $res = false;
    try {
        $res = $conn->exec($query);
    } catch(PDOException $e) {
        updateDbErrorLog("SQL query failed: " . $e->getMessage(), $query);
    }
    if ($res == false && $res !== 0) {
        updateDbErrorLog(getLastError($conn), $query);
        return false;
    }
    return true;
}

?>