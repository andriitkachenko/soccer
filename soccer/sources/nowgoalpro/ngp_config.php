<?php
    const NGP_BASE_URL = 'http://www.nowgoal.pro';
    const START_TRACKING = 600; // start tracking at 10 min
    const BREAK_TIME = 1200; // 20 min for break
    const STAT_UPDATE_INTERVAL = 60;
    define('MAX_PROCESSING_TIME', (int)(STAT_UPDATE_INTERVAL * 0.75));
?>