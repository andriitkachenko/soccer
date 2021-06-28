<?php
declare(strict_types=1);

require_once __DIR__ . '/../../php/logs.php';
require_once __DIR__ . '/../../php/time.php';
require_once __DIR__ . '/nowgoalpro.php';
require_once __DIR__ . '/ngp_db_manager.php';
require_once __DIR__ . '/../../services/db/db_connection.php';
require_once __DIR__ . '/../../services/db/db_settings.php';
require_once __DIR__ . '/../../services/parsehub/parsehub_utils.php';


function log_and_die_if($condition, $log) {
    if ($condition) {
        parsehub_hook_log("error", $log);
        die();
    }
}

parsehub_hook_log("started");

$runData = file_get_contents('php://input');
log_and_die_if(empty($runData), "Empty run data");

parse_str($runData, $run);

$ready = isset($run["data_ready"]) && $run["data_ready"] === "1" 
      && isset($run["status"]) && $run["status"] === "complete";
if (!$ready) {
    parsehub_hook_log("not ready", reduceRunData($run));    
    die();
}

parsehub_hook_log("ready", json_encode(reduceRunData($run)));

log_and_die_if(empty($run['run_token']), "Could not find run token: " . json_encode($run));

$token = $run['run_token'];

$ph = new ParseHub('ngp', PH_PROJECT_TOKEN, PH_API_KEY);

$ok = !empty($run['start_running_time']) && strtolower($run['is_empty']) === "false";
if (!$ok) {
    $ph->delete_run($token);
    log_and_die_if(true, "Empty run result or start time - delete run");
}

$phData = $ph->get_data($token);
$ph->delete_run($token);
log_and_die_if(empty($phData), "No data from Parse Hub");

update_parsehub_response_file($phData['raw']);

$gameData = $phData['data'];

$ngp = new NowGoalPro();

$games = $ngp->getParseHubGames($gameData);
log_and_die_if(empty($games), "No game found");

$result = file_put_contents(DATA_FILE, json_encode($games));

parsehub_hook_log("Received " . count($games) . " games");

$dbConn = new DbConnection(new DbSettings(false));
log_and_die_if(!$dbConn->connected(), "DB connection failed");

$dbManager = new NgpDbManager($dbConn);
$ngp->setDbManager($dbManager);

$ok = $ngp->updateNewGames($games);

parsehub_hook_log("New games update - " . logs2s($ok, $dbManager->getLastError(), "\n")); //humanizeBool($ok));

?>