<?php
declare(strict_types=1);

chdir(__DIR__ . '/../..');

require_once 'php/logs.php';

$ok = $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cron']) && $_POST['cron'] == CRON_KEY;
if (!$ok) {
    updateCronLog("!!!  UNKNOWN CRON REQUEST !!!", json_encode(array_merge($_REQUEST, $_SERVER)));
    echo 'Unknown request';
    die();
}

require_once 'sources/nowgoalpro/nowgoalpro.php';

$isCron1 = true;
$minute = @intval(date("i"));
$isCron15 = in_array($minute, [10, 25, 40, 55]);
$sources = [ 
    new NowGoalPro()
]; 

foreach($sources as $s) {
    if (true || $isCron15) {
        if ($s->isParseHubClient()) {
            $runData = $s->runParseHubProject();
            if (empty($runData['ok'])) {
                echo "Project run failed";
            }
            echo date("d-m-Y H:i:s");
            echo "<br /><br />";
            echo $runData['run'];
            echo "<br /><br />";
            echo "Attempts: " . $runData['attempts'];
            echo "<br /><br />";
            echo $runData['logged'] ? "Log successful" : "Log failed" ;        
        }
    }
    if ($isCron1) {
        $s->runOneMinuteUpdate();
    }
}

die();

/*
require_once __DIR__ . '/../logs.php';
require_once __DIR__ . '/../log/log.php';
require_once __DIR__ . '/livescores/livescores.php';
require_once __DIR__ . '/livescores/db_manager.php';

require_once __DIR__ . '/parsehub/parsehub_utils.php';


die();









    $debug = !empty($_POST['debug']);
    $log = new Log();

    $local = !empty($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false;
    $dbConn = makeDbConnection($local);

    $ok = false;
    if (!empty($dbConn)) {
        $dbManager = new DbManager($dbConn);
        runLivescores($dbManager, $log);
        $ok = !$log->hasError();
        $res = $dbManager->insertLog($log);
        if ($res !== true) {
            $log->append("Log saving failure: Query: $res; Error: " . json_encode($dbConn->errorInfo()));
        }
    } else {
        $log->append("DB connection failure", LOG_TYPE_ERROR);
    }
    http_response_code( $ok ? 200 : 500);
    echo humanizeBool($ok);
    if ($log->hasError()) {
        updateCronLog("Cron 1-minute log with error", $log->get());
    }
    if ($debug) {
        echo $log->get();
    }
    die();
*/    
?>

