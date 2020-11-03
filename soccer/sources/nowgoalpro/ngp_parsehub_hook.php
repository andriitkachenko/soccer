<?php
declare(strict_types=1);

require_once __DIR__ . '/../../php/logs.php';
require_once __DIR__ . '/../../php/time.php';
require_once __DIR__ . '/nowgoalpro.php';
require_once __DIR__ . '/ngp_db_manager.php';
require_once __DIR__ . '/../../services/db/db_connection.php';
require_once __DIR__ . '/../../services/db/db_settings.php';


$runData = file_get_contents('php://input');

if (empty($runData)) {
    parsehubLog("NGP hook", "Empty run data");
    die();
}

$runData = urldecode($runData);
$runData = explode("&", $runData);

$ready = in_array("data_ready=1", $runData) && in_array("status=complete", $runData);

if (!$ready) {
    die();
}

parsehubLog("NGP hook", json_encode($runData));

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
    parsehubLog("NGP hook", 'Could not find run token or start time');
    die();
}

$ph = new ParseHub(PH_PROJECT_TOKEN, PH_API_KEY);

if (!in_array("is_empty=False", $runData)) {
    parsehubLog("NGP hook", 'Empty run result - delete run');
    $res = $ph->deleteParseHubRun($run['token']);
    die();
}

$gameData = $ph->getData($run['token']);

if (empty($gameData)) {
    parsehubLog("NGP hook", 'No data from Parse Hub');    
    die();
}

$ngp = new NowGoalPro();

$games = $ngp->getParseHubGames($gameData);

if (empty($games)) {
    parsehubLog("NGP hook", 'No game found');    
    die();
}

$result = file_put_contents(DATA_FILE, json_encode($games));

parsehubLog("NGP hook", "Received " . count($games) . " games");

$dbConn = new DbConnection(new DbSettings(false));
if (!$dbConn->connected()) {
    parsehubLog("NGP hook", "DB connection failed");
}
$dbManager = new NgpDbManager($dbConn);
$ngp->setDbManager($dbManager);

$ok = $ngp->updateNewGames($games);

parsehubLog("NGP hook", "New games update - " . humanizeBool($ok));

?>