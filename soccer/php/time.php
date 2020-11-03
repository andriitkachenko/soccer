<?php
declare(strict_types=1);

function time2minuteStamp($time = null) : int {
    $time = empty($time) ? time() : $time;
    return floor($time / 60.);
}

function string2Timestamp($string) : int {
    //2020-08-02T10:45:28
    $stamp = DateTime::createFromFormat('Y-m-d\TH:i:s', $string)->getTimestamp();
    if (empty($stamp)) {
        return time();
    }
    return $stamp;
}

function string2MinuteStamp($string) : int {
    return time2minuteStamp(string2Timestamp($string));
}

function time2datetime($t = null) {
    return date("Y-m-d H:i:s", empty($t) ? time() : $t); 
}

?>