<?php

require_once __DIR__ . '/../../php/utils.php';
require_once __DIR__ . '/ngp_db_manager.php';

function getLiveLastStats() {
    $dbManager = new NgpDbManager(new DbConnection(new DbSettings(isLocalhost())));
    $stats = $dbManager->loadLiveLastStats();
    $history1 = $dbManager->loadHistoryStats(5);
    $history2 = $dbManager->loadHistoryStats(10);
    foreach($stats  as $id => $s) {
        if (isset($history1[$id])) {
            $stats[$id]['history1'] = $history1[$id];
        }
        if (isset($history2[$id])) {
            $stats[$id]['history2'] = $history2[$id];
        }
    }
    return $stats;
}

?>