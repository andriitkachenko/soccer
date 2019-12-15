<?php

require_once __DIR__ . '/config.php';

function getLog($logFile, $archiveFile) {
    $log = "";
    if (file_exists($logFile)) {
        $fsize = @filesize($logFile);
        if ($fsize) {
            if ($fsize > MAX_LOG_SIZE) {
                unlink($archiveFile);
                rename($logFile, $archiveFile);
            } else {
                $log = file_get_contents($logFile);
                if (!$log) {
                    $log = "";
                }
            }
        }
    }
    return $log;
}

function updateParsehubLog($operation, $data) {
    $old = getLog(PARSEHUB_LOG, PARSEHUB_LOG_ARCHIVE);
    $items = [
        $operation, 
        date("Y-m-d H:i:s"), 
        $data
    ];
    return file_put_contents(PARSEHUB_LOG, implode("\n", $items) . "\n\n" .$old);
}

function updateAccessLog() {
    $old = getLog(ACCESS_LOG, ACCESS_LOG_ARCHIVE);
    $items = [
        date("Y-m-d H:i:s"), 
        'Remote Addr: ' . (isset($_SERVER['REMOTE_ADDR'])  ?  $_SERVER['REMOTE_ADDR'] : "Unknown"), 
        'Remote Host: ' . (isset($_SERVER['REMOTE_HOST'])  ?  $_SERVER['REMOTE_HOST'] : "Unknown"), 
        'User Agent:  ' . (isset($_SERVER['HTTP_USER_AGENT'])  ?  $_SERVER['HTTP_USER_AGENT'] : "Unknown")
    ];
    return file_put_contents(ACCESS_LOG, implode(" *** ", $items) . "\n" . $old);
}

function updateCronLog($title, $data) {
    $old = getLog(CRON_LOG, CRON_LOG_ARCHIVE);
    $items = [
        date("Y-m-d H:i:s"), 
        $title, 
        $data
    ];
    return file_put_contents(CRON_LOG, implode("\n", $items) . "\n\n" .$old);
}

function updateLastParsehubResponseFile($data) {
    return file_put_contents(LAST_PARSEHUB_RESPONSE_FILE, $data);
}

function updateDbErrorLog($error, $query = null) {
    $old = getLog(DB_ERROR_LOG, DB_ERROR_ARCHIVE);
    $items = [
        date("Y-m-d H:i:s"), 
        $error
    ];
    if (!empty($query)) {
        $items[] = 'Query: ' . $query;
    }
    return file_put_contents(DB_ERROR_LOG, implode("\n", $items) . "\n\n" . $old);
}
?>