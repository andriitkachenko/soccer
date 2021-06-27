<?php

require_once __DIR__ . '/../../php/utils.php';
require_once __DIR__ . '/ngp_db_manager.php';

function getLiveLastStats($dbManager) {
    $stats = $dbManager->loadLiveLastStats();
    $history2 = $dbManager->loadLiveHistoryStats(10);
    $halftime = $dbManager->loadLiveHalfTimeStats();
    foreach($stats as $id => $s) {
        if (isset($history2[$id])) {
            $stats[$id]['history2'] = $history2[$id];
        }
        if (isset($halftime[$id])) {
            $stats[$id]['ht'] = $halftime[$id];
        }
    }
    return $stats;
}

?>