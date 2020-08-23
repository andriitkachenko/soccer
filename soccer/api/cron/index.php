<?php

require_once __DIR__ . '/cron_config.php';

if (!CRON_ENABLED) {
    echo 'Cron job disabled';
    die();
}

require_once __DIR__ . '/cron.php';
    
?>

