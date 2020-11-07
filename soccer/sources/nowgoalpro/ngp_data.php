<?php

require_once __DIR__ . '/../../php/utils.php';
require_once __DIR__ . '/ngp_db_manager.php';

function getLiveLastStats() {
    $dbManager = new NgpDbManager(new DbConection(new DbSettings(isLocalhost())));
    $stats = $dbManager->loadLiveLastStats();
    return $stats;
}

?>