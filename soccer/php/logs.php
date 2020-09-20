<?php

require_once __DIR__ . '/config.php';
const SEPARATOR = ' ~~~ ';

function updateLog($logFile, $items) {
    if (!file_exists($logFile)) {
        return false;
    }
    $data = is_array($items) ? implode(SEPARATOR, $items) : $items;
    $log = file_get_contents($logFile);
    $log = time2DateTime() . SEPARATOR . $data . "\n". $log;     
    return file_put_contents($logFile, substr($log, 0, MAX_LOG_SIZE));
}

function updateParseHubLog($operation, $data) {
    $items = [
        $operation, 
        $data
    ];
    return updateLog(PARSEHUB_LOG, $items);
}

function updateAccessLog() {
    $items = [
        'Remote Addr: ' . (isset($_SERVER['REMOTE_ADDR'])  ?  $_SERVER['REMOTE_ADDR'] : "Unknown"), 
        'Remote Host: ' . (isset($_SERVER['REMOTE_HOST'])  ?  $_SERVER['REMOTE_HOST'] : "Unknown"), 
        'User Agent:  ' . (isset($_SERVER['HTTP_USER_AGENT'])  ?  $_SERVER['HTTP_USER_AGENT'] : "Unknown")
    ];
    return updateLog(ACCESS_LOG, $items);
}

function updateCronLog($title, $data = "") {
    $items = [ $title ];
    if (!empty($data)) {
        $items[] = $data;
    }
    return updateLog(CRON_LOG, $items);
}

function updateLastParsehubResponseFile($data) {
    return file_put_contents(LAST_PARSEHUB_RESPONSE_FILE, $data);
}

function updateDbErrorLog($error, $query = null) {
    $items = [
        $error
    ];
    if (!empty($query)) {
        $items[] = 'Query: ' . $query;
    }
    return updateLog(DB_ERROR_LOG, $items);
}
?>