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

$stopTime = time() + MAX_PROCESSING_TIME;

$minute = @intval(date("i"));
$isCron5 = ($minute % 5) == 0;  //in_array($minute, [10, 25, 40, 55]);
$dbConn = new DbConnection(new DbSettings(isLocalhost()));
if (!$dbConn->connected()) {
    errorLog("NGP cron ", "Could not connect to DB");
}

$ngp = new NowGoalPro();
$dbManager  = new NgpDbManager($dbConn);
$ngp->setDbManager($dbManager);
$sources = [ $ngp ]; 

foreach($sources as $s) {
    if ($isCron5) {
        if ($s->isParseHubClient()) {
            $runData = $s->runParseHubProject();
            updateCronLog("Run ParseHub from cron", json_encode($runData));
            $info = [
                time2datetime(),
                "Parse Hub project run " . (empty($runData['ok']) ? 'failed' : "OK"),
                "Attempts: " . $runData['attempts'],
                $runData['logged'] ? "Log successful" : "Log failed",
            ];
            echo implode(" ~~~ ", $info);
        }
    }
    if (!$dbConn->connected()) {
        continue;
    }
    $ok = $s->runOneMinuteUpdate($stopTime);
    $log = logs2s($ok, $dbManager->getLastError(), ' ~~~ ');
    updateCronLog("1-minute update", $ok ? humanizeBool($ok) : $log);
    echo humanizeBool($ok);
}

die();

?>

