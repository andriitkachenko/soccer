<?php
declare(strict_types=1);

require_once __DIR__ . '/../../php/logs.php';
require_once __DIR__ . '/../../php/time.php';
require_once __DIR__ . '/nowgoalpro.php';
require_once __DIR__ . '/ngp_db_manager.php';
require_once __DIR__ . '/../../services/db/db_connection.php';
require_once __DIR__ . '/../../services/db/db_settings.php';
require_once __DIR__ . '/../../services/parsehub/parsehub_utils.php';


function logAndDieIf($condition, $log) {
    if ($condition) {
        parsehubLog("NGP hook - error", $log);
        die();
    }
}

$runData = file_get_contents('php://input');
logAndDieIf(empty($runData), "Empty run data");

parse_str($runData, $run);

$ready = isset($run["data_ready"]) && $run["data_ready"] === "1" 
      && isset($run["status"]) && $run["status"] === "complete";
if (!$ready) {
    die();
}

parsehubLog("NGP hook - run data", json_encode(reduceRunData($run)));

logAndDieIf(empty($run['run_token']), "Could not find run token");

$token = $run['run_token'];

$ph = new ParseHub(PH_PROJECT_TOKEN, PH_API_KEY);

$ok = !empty($run['start_running_time']) && strtolower($run['is_empty']) === "false";
if (!$ok) {
    $ph->deleteParseHubRun($token);
    logAndDieIf(true, "Empty run result or start time - delete run");
}

$phData = $ph->getData($token);
$ph->deleteParseHubRun($token);
logAndDieIf(empty($phData), "No data from Parse Hub");

updateLastParsehubResponseFile($phData['raw']);

$gameData = $phData['data'];

$ngp = new NowGoalPro();

$games = $ngp->getParseHubGames($gameData);
logAndDieIf(empty($games), "No game found");

$result = file_put_contents(DATA_FILE, json_encode($games));

parsehubLog("NGP hook", "Received " . count($games) . " games");

$dbConn = new DbConnection(new DbSettings(false));
logAndDieIf(!$dbConn->connected(), "DB connection failed");

$dbManager = new NgpDbManager($dbConn);
$ngp->setDbManager($dbManager);

$ok = $ngp->updateNewGames($games);

parsehubLog("NGP hook", "New games update - " . logs2s($ok, $dbManager->getLastError(), "\n")); //humanizeBool($ok));

?>