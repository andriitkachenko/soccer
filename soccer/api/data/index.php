<?php

require_once __DIR__ . '/../../sources/nowgoalpro/ngp_data.php';
require_once __DIR__ . '/../../php/logs.php';
require_once __DIR__ . '/../../php/utils.php';


function response($code, $data) {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data);
}

$data = $_POST;

if (empty($data)) {
    $data = json_decode(file_get_contents('php://input'), true);
}

accessLog();

$operation = getIfSet($data, ['op']);

switch($operation) {
    case 'last_stat': 
        $dbManager = new NgpDbManager(new DbConnection(new DbSettings(isLocalhost())));        
        $stats = getLiveLastStats($dbManager);
        response(200, $stats);
        break;
    default: 
        $error = [
            'error' => 'Unknown request',
            'data' => $data
        ];
        errorLog("data", json_encode($error));
        response(400, $error);
        break;
}
die();
?>