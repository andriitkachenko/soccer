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
    if (empty($logFile)) {
        return;
    }
    $log = file_exists($logFile['name']) ? file_get_contents($logFile['name']) : '';
    $data = is_array($items) ? implode(SEPARATOR, $items) : $items;
    $log = time2datetime() . SEPARATOR . $data . "\n". $log;     
    $res = file_put_contents($logFile['name'], $sizeLimit ? substr($log, 0, $logFile['size']) : $log);

    return $res !== false;
}

function parsehubLog($operation, $data) {
    $items = [
        $operation, 
        $data
    ];
    return updateLog(getLogFile('parsehub'), $items);
}

function accessLog() {
    $items = [
        'Remote Addr: ' . (isset($_SERVER['REMOTE_ADDR'])  ?  $_SERVER['REMOTE_ADDR'] : "Unknown"), 
        'Remote Host: ' . (isset($_SERVER['REMOTE_HOST'])  ?  $_SERVER['REMOTE_HOST'] : "Unknown"), 
        'User Agent:  ' . (isset($_SERVER['HTTP_USER_AGENT'])  ?  $_SERVER['HTTP_USER_AGENT'] : "Unknown")
    ];
    return updateLog(getLogFile('access'), $items);
}

function updateCronLog($title, $data = "") {
    $items = [ $title ];
    if (!empty($data)) {
        $items[] = $data;
    }
    return updateLog(getLogFile('cron'), $items);
}

function updateLastParsehubResponseFile($data) {
    return file_put_contents(LAST_PARSEHUB_RESPONSE_FILE, $data);
}

function errorLog($title, $error = "") {
    $items = [ $title ];
    if (!empty($error)) {
        $items = array_merge($items, is_array($error) ? $error : [$error]);
    }
    return updateLog(getLogFile('error'), $items, false);
}

function getLogFile($name) {
    if (empty(LOG_FILES[$name])) {
        return null;
    }
    $log = LOG_FILES[$name];
    $log['name'] = LOG_DIR . $log['name'];
    $log['size'] = pow(1024, 2) * $log['size'];
    return $log;
}
?>