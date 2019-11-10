<?php
    require_once __DIR__ . '/parsehub_utils.php';

    $ok = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cron']);
    if (!$ok) {
        updateCronLog("!!!  UNKNOWN CRON REQUEST !!!", json_encode(array_merge($_REQUEST, $_SERVER)));
        echo 'Unknown request';
        die();
    }
    // run parse hub update at XX:10, XX:25, XX:40, XX:55 
    $minute = @intval(date("i"));
    if (in_array($minute, [10, 25, 40, 55])) {
        $runData = getRunTokenSeries();
        if (!empty($runData['ok'])) {
            updateCronLog("15-minute update", "OK");
        } else {
            updateCronLog("15-minute update", "Failed\n" . json_encode($runData));
        }
    }
    // run update of live games
    updateCronLog("1-minute update", "OK");
?>