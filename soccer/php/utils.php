<?php

const DB_NULL = 'NULL';
$DEBUG_MODE = false;

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

function quotize($value) {
    return "'" . $value . "'";
}

function getValue($value) {
    if (!is_array($value)) {
        return $value;
    }
    if (count($value) < 2) {
        return $value[0];
    }
    if (!is_object($value[0]) && !is_array($value[0])) {
        return null;
    }
    $val = null;
    foreach($value as $i => $v) {
        if (!$i) {
            $val = $v;
            continue;
        }
        $val = (array)$val;
        if (!isset($val[$v])) {
            $val = null;
            break;
        }
        $val = $val[$v];
    }
    return $val;
}

function dbInt($value, $nullable = false) {
    $value = getValue($value);
    $isNull = is_null($value);
    if ($nullable && $isNull) {
        return DB_NULL;
    }
    return !$isNull ? intval($value) : 0;
}

function dbDatetime($value, $nullable = false) {
    $value = getValue($value);
    $isNull = is_null($value);
    if ($nullable && $isNull) {
        return DB_NULL;
    }
    return quotize(time2datetime(!$isNull ? $value : 0));
}

function dbString($value, $nullable = false) {
    $value = getValue($value);
    $isEmpty = empty($value);
    if ($nullable && $isEmpty) {
        return DB_NULL;
    }
    return quotize(!$isEmpty ? $value : "");
} 

function dbJSon($value, $nullable = false) {
    $value = getValue($value);
    $isEmpty = empty($value);
    if ($nullable && $isEmpty) {
        return DB_NULL;
    }
    return quotize(json_encode(!$isEmpty ? $value : []));
} 

function dbHash($value) {
    $hash = substr(sha1($value), 0, 10);
    return quotize($hash);
} 

function makeInsertValues($dbValues) {
    if (!is_array($dbValues)) return false;
    if (empty($dbValues)) return "";
    $values = [];
    foreach($dbValues as $v) {
        $values[] =  "(" . implode(',', $v) . ")";
    }
    return implode(',', $values); 
}

function isLocalhost() {
    return !empty($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false;
}

function errorPrint($condition, $data) {
    if ($condition === false) {
        print_r($data . PHP_EOL);
        die;
    }
}

function normalizeData($data) {
    return str_replace("'", "", $data);
}

function makeStatJson($g, $withMinute = true) {
    if (empty($g->stat) || ($withMinute && empty($g->status->min))) {
        return null;
    }
    return json_encode(array_merge(
        $withMinute ? [ 'min' => $g->status->min] : [],
        (array)$g->stat
    ));
}

function addObjectProperty($obj, $key, $value) {
    if (empty($key)) {
        return false;
    }
    $arr = (array)$obj;
    $arr[$key] = $value;
    return (object)$arr;
}

function getIfSet($arr, $key, $def = null) {
    if (empty($arr) || !is_array($arr) || !is_object($arr)) 
        return null;

    $arr = (array)$arr;
    
    if (!is_array($key)) {
        return isset($arr[$key]) ? $arr[$key] : $def;
    }

    foreach($key as $k) {
        if (isset($arr[$key])) {
            $arr = $arr[$key];
        } else {
            return $def;
        }
    }
    return $arr;
}
?>