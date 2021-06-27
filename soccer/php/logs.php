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

function update_log_file($logFile, $items, $sizeLimit = true) {
    if (empty($logFile)) {
        return;
    }
    $log = file_exists($logFile['name']) ? file_get_contents($logFile['name']) : '';
    $data = is_array($items) ? implode(SEPARATOR, $items) : $items;
    $log = time2datetime() . SEPARATOR . $data . "\n". $log;     
    $res = file_put_contents($logFile['name'], $sizeLimit ? substr($log, 0, $logFile['size']) : $log);

    return $res !== false;
}

function update_log($log_info, $title, $data, $sizeLimit = true) {
    $items = $title !== "" ? [ $title ] : [];
    if (!empty($data)) {
        $items = array_merge($items, is_array($data) ? $data : [$data]);
    }
    return update_log_file(get_log_file_info($log_info), $items, $sizeLimit);
}

function parsehub_run_log($operation, $data = "") {
    return update_log(PARSEHUB_RUN_LOG, $operation, $data);
}

function parsehub_hook_log($operation, $data = "") {
    return update_log(PARSEHUB_HOOK_LOG, $operation, $data);
}

function access_log() {
    $items = [
        'Remote Addr: ' . (isset($_SERVER['REMOTE_ADDR'])  ?  $_SERVER['REMOTE_ADDR'] : "Unknown"), 
        'Remote Host: ' . (isset($_SERVER['REMOTE_HOST'])  ?  $_SERVER['REMOTE_HOST'] : "Unknown"), 
        'User Agent:  ' . (isset($_SERVER['HTTP_USER_AGENT'])  ?  $_SERVER['HTTP_USER_AGENT'] : "Unknown")
    ];
    return update_log(ACCESS_LOG, "", $items);
}

function errorLog($title, $error = "") {
    return update_log(ERROR_LOG, $title, $error, false);
}

function cron_log($title, $data = "") {
    return update_log(CRON_LOG, $title, $data);
}

function update_parsehub_response_file($data) {
    return file_put_contents(LAST_PARSEHUB_RESPONSE_FILE, $data);
}

function get_log_file_info($logInfo) {
    $log_file_info['name'] = LOG_DIR . $logInfo['name'];
    $log_file_info['size'] = pow(1024, 2) * $logInfo['size'];
    return $log_file_info;
}

?>