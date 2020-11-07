<?php

require_once __DIR__ . '/../../sources/nowgoalpro/ngp_data.php';

header('Content-Type: application/json');

$operation = getIfSet($_POST, ['op']);

switch($operation) {
    case 'last_stat': 
        $stats = getLiveLastStats();
        echo json_encode($stats);
        break;
    default: 
        echo 'Unknown request';
        break;
}

?>