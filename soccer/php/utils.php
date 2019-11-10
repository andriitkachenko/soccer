<?php

function humanizeBool($bool) {
    return $bool ? 'OK' : 'Failure';
}

function appendError($error) {
    global $lastError;
    $lastError = $lastError . "\n\n" . $error;
}

function getMinuteTimestamp() {
    return floor(time() / 60.);
}
?>