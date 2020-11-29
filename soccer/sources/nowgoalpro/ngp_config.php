<?php
    const NGP_BASE_URL = 'http://www.nowgoal.pro';
    
    const START_TRACKING_MINUTE = 10; // start tracking at 10 min
    const BREAK_TIME = 1200; // 20 min for break
    const MAX_NO_SHOT_MINUTE = 30; // set 'non-trackable' status if at this minute we have to 'shots' statistic 

    const STAT_UPDATE_INTERVAL = 60;
    define('MAX_PROCESSING_TIME', (int)(STAT_UPDATE_INTERVAL * 0.8));
?>