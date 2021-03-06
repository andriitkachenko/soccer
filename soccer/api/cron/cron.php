<?php
declare(strict_types=1);

chdir(__DIR__ . '/../..');

require_once 'php/logs.php';
require_once 'php/time.php';

$ok = $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cron']) && $_POST['cron'] == CRON_KEY;
if (!$ok) {
    errorLog("!!!  UNKNOWN CRON REQUEST !!!", json_encode(array_merge($_REQUEST, $_SERVER)));
    echo 'Unknown request';
    die();
}


require_once 'sources/nowgoalpro/nowgoalpro.php';
require_once 'services/db/db_connection.php';
require_once 'php/utils.php';

$dryrun = isset($_POST['dryrun']) && $_POST['dryrun'] == '1';
$debug = isset($_POST['debug']) && $_POST['debug'] == '1';

$info = ($debug ? 'debug ' : '') . ($dryrun ? 'dryrun' : '');

cron_log("============================  started  ============================ $info");

$stopTime = time() + MAX_PROCESSING_TIME;

$minute = @intval(date("i"));
$isParsehubTime = $debug || ($minute % CRON_PARSEHUB_INTERVAL) === 0;
$dbConn = new DbConnection(new DbSettings(isLocalhost()));
if (!$dbConn->connected()) {
    errorLog("NGP cron ", "Could not connect to DB");
}

$ngp = new NowGoalPro();
$dbManager  = new NgpDbManager($dbConn);
$ngp->setDbManager($dbManager);
$sources = [ $ngp ]; 

foreach($sources as $s) {

    if ($isParsehubTime && $s->isParseHubClient()) {
        $ph = $s->get_parsehub();
        $ph->clean_up_project();
        if ($dryrun) {
            continue;
        }        
        $runData = $ph->run_project();
        cron_log("Run ParseHub from cron", json_encode($runData));
        $info = [
            time2datetime(),
            "Parse Hub project run " . (empty($runData['ok']) ? 'failed' : "OK"),
            "Attempts: " . $runData['attempts'],
            $runData['logged'] ? "Log successful" : "Log failed",
        ];
        echo implode(" ~~~ ", $info);
    }
    if ($dryrun || !$dbConn->connected()) {
        continue;
    }
    cron_log("1-minute update started");
    $ok = $s->runOneMinuteUpdate($stopTime);
    $fullLog = CRON_FULL_LOG || !$ok;
    $log = $fullLog ? logs2s($ok, $dbManager->getLastError(), "\n") : humanizeBool($ok);
    cron_log("1-minute update finished", $log);
    echo humanizeBool($ok);
}

die();

?>

