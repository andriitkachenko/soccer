<?php
declare(strict_types=1);

require_once __DIR__ . '/../../php/logs.php';
require_once __DIR__ . '/../../php/utils/time.php';
require_once __DIR__ . '/nowgoalpro.php';

$runData = file_get_contents('php://input');

updateParsehubLog("NGP hook", "Start");

if (empty($runData)) {
    updateParsehubLog("NGP hook", "Empty run data");
    die();
}

$runData = urldecode($runData);
$runData = explode("&", $runData);

updateParsehubLog("NGP hook", json_encode($runData));

$ready = in_array("data_ready=1", $runData) && in_array("status=complete", $runData);

if (!$ready) {
    die();
}

updateParsehubLog("NGP hook", 'Run data ready');

$run = [];
foreach ($runData as $line) {
    $line = trim($line);
    if (strpos($line, "run_token=") === 0) {
        $run['token'] = trim(str_replace("run_token=", "", $line));
    } else if (strpos($line, "start_running_time=") === 0) {
        $run['start_time'] = trim(str_replace("start_running_time=", "", $line));
    }
}

$ok = !empty( $run['token']) && !empty( $run['start_time']);

if (!$ok) {
    updateParsehubLog("NGP hook", 'Could not find run token or start time');
    die();
}

$ph = new ParseHub(PH_PROJECT_TOKEN, PH_API_KEY);

if (!in_array("is_empty=False", $runData)) {
    updateParsehubLog("NGP hook", 'Empty run result - delete run');
    $res = $ph->deleteParseHubRun($run['token']);
    die();
}

updateParsehubLog("NGP hook", "Start getting game data");

$gameData = $ph->getData($run['token']);
$gameDataTime = string2Timestamp($run['start_time']);

if (empty($gameData)) {
    updateParsehubLog("NGP hook", 'No data from Parse Hub');    
    die();
}

updateParsehubLog("NGP hook", "Received game data");

$ngp = new NowGoalPro();
$games = $ngp->getParseHubGames($gameData);

if (empty($games)) {
    updateParsehubLog("NGP hook", 'No game found');    
    die();
}

$result = file_put_contents(DATA_FILE, json_encode($games));

updateParsehubLog("NGP hook", "Received " . count($games) . " games");

$ok = $ngp->updateGames($games, $gameDataTime);

die();

/*

//2019-11-09T17:55:03
$anchorTime = DateTime::createFromFormat('Y-m-d\TH:i:s', $run['start_time'])->getTimestamp();

$games = getParseHubData($run['token']);

$result = file_put_contents(DATA_FILE, json_encode($games));
$dbResult = humanizeBool(saveGamesToDB($games, $anchorTime));

updateParsehubLog("ParseHub webhook save", "db: $dbResult, file: $result" );

*/
/*
curl -X POST "https://scoreslive.000webhostapp.com/php/parsehub_webhook.php" -H "Content-Type: application/x-www-form-urlencoded" -d '{"some":"json"}'
*/

?>