<?php

/*
    /usr/bin/php /home/u421817030/public_html/api/cron/index.php
    
    wget -O /dev/null --post-data 'cron=2HmcuJwZ5w' http://livesoccer.96.lt/api/cron/index.php
*/

    const CRON_ENABLED = 1;
    const CRON_PARSEHUB_INTERVAL = 3; // minutes
    const CRON_KEY = '2HmcuJwZ5w';
    const CRON_FULL_LOG = 1; // if 0  - log only OK on success
?>