<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/time.php';
const SEPARATOR = ' ~~~ ';

$logs = [];

function addLog($data) {
    global $logs;
    $logs[] = $data;
}

function logs2s($ok, $lastDbError, $separator = PHP_EOL)  {
    global $logs;
    return implode($separator, 
        array_merge(
            [humanizeBool($ok)], 
            $logs,
            $ok || !$lastDbError ? [] : [$lastDbError]
        ));
}

function logPrint($ok, $lastDbError) {
    print_r(logs2s($ok, $lastDbError));
}

function updateLog($logFile, $items, $sizeLimit = true) {
    $log = file_exists($logFile) ? file_get_contents($logFile) : '';
    $data = is_array($items) ? implode(SEPARATOR, $items) : $items;
    $log = time2datetime() . SEPARATOR . $data . "\n". $log;     
    return file_put_contents($logFile, $sizeLimit ? substr($log, 0, MAX_LOG_SIZE) : $log);
}

function parsehubLog($operation, $data) {
    $items = [
        $operation, 
        $data
    ];
    return updateLog(PARSEHUB_LOG, $items);
}

function accessLog() {
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

function errorLog($title, $error = "") {
    $items = [ $title ];
    if (!empty($error)) {
        $items = array_merge($items, is_array($error) ? $error : [$error]);
    }
    return updateLog(ERROR_LOG, $items, false);
}
?>