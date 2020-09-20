<?php

function humanizeBool($bool) {
    return $bool ? 'OK' : 'Failure';
}

function appendError($error) {
    global $lastError;
    $lastError = $lastError . "\n\n" . $error;
}

function getError() {
    global $lastError;
    return $lastError;
}

function getMinuteTimestamp($time) {
    $time = empty($time) ? time() : $time;
    return floor($time / 60.);
}

function dbInt($arr, $key, $nullable = false) {
    $arr = (array)$arr;
    return $nullable && !isset($arr[$key]) 
        ? 'NULL'
        : (isset($arr[$key]) ? intval($arr[$key]) : 0);
}

function dbDatetime($arr, $key, $nullable = false) {
    $arr = (array)$arr;
    return $nullable && !isset($arr[$key]) 
        ? 'NULL'
        : ("'" . time2datetime(isset($arr[$key]) ? $arr[$key] : 0) . "'");
}

function dbString($arr, $key, $nullable = false) {
    $arr = (array)$arr;
    return $nullable && empty($arr[$key]) 
        ? 'NULL'
        : ("'" . (!empty($arr[$key]) ? $arr[$key] : "") . "'");
} 
?>