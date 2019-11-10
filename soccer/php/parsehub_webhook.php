<?php

require_once __DIR__ . '/parsehub_utils.php';
require_once __DIR__ . '/db/db_operations.php';
require_once __DIR__ . '/games.php';
require_once __DIR__ . '/utils.php';

$response = file_get_contents('php://input');
if (empty($response)) 
    die();

$response = urldecode($response);
$response = explode("&", $response);

updateParsehubLog("ParseHub webhook", json_encode($response));

if (!in_array("data_ready=1", $response) || !in_array("status=complete", $response)) {
    die();
}
if (!in_array("is_empty=False", $response)) {
    deleteParseHubRun($runToken);
    getRunTokenSeries();
    die();
}
$run = [];
foreach ($response as $line) {
    $line = trim($line);
    if (strpos($line, "run_token=") === 0) {
        $run['token'] = trim(str_replace("run_token=", "", $line));
    } else if (strpos($line, "start_running_time=") === 0) {
        $run['start_time'] = trim(str_replace("start_running_time=", "", $line));
    }
}

//2019-11-09T17:55:03
$ahchorTime = DateTime::createFromFormat('Y-m-d\TH:i:s', $run['start_time'])->getTimestamp();

$games = getParseHubData($run['token']);

$result = file_put_contents(DATA_FILE, json_encode($games));
$dbResult = humanizeBool(saveGamesToDB($games, $ahchorTime));

updateParsehubLog("ParseHub webhook save", "db: $dbResult, file: $result" );

/*
curl -X POST "https://scoreslive.000webhostapp.com/php/parsehub_webhook.php" -H "Content-Type: application/x-www-form-urlencoded" -d '{"some":"json"}'
*/
?>