<?php

function reduceRunData($run) {
    $params = ['status', 'run_token', 'data_ready', 'is_empty', 'start_time', 'start_running_time'];
    $log = [];
    foreach($params as $p) {
        if (isset($run[$p])) {
            $log[$p] = $run[$p];
        }
    }
    return $log;
}

?>
