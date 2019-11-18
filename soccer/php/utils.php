<?php

function humanizeBool($bool) {
    return $bool ? 'OK' : 'Failure';
}

function appendError($error) {
    global $lastError;
    $lastError = $lastError . "\n\n" . $error;
}

function getMinuteTimestamp($time) {
    $time = empty($time) ? time() : $time;
    return floor($time / 60.);
}
?>