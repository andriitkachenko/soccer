<?php

const DATA_FILE = __DIR__ . '/../data/games.json';

const ACCESS_LOG = __DIR__ . "/../logs/access.log";
const ACCESS_LOG_ARCHIVE = __DIR__ . "/../logs/access_archive.log";
const PARSEHUB_LOG = __DIR__ . "/../logs/parsehub.log";
const PARSEHUB_LOG_ARCHIVE = __DIR__ . "/../logs/parsehub_archive.log";
const CRON_LOG = __DIR__ . "/../logs/cron.log";
const CRON_LOG_ARCHIVE = __DIR__ . "/../logs/cron_archive.log";

const PARSEHUB_RUN_PROJECT_URL = 'https://www.parsehub.com/api/v2/projects/txg_T0WpxYTc/run';
const PARSEHUB_RUN_DATA_URL= 'https://www.parsehub.com/api/v2/runs/';
const PARSEHUB_API_KEY = "tbXb7zgCH0L8";
const DATA_URL = "http://data.unogoal.life/detail.aspx?ID=";


define('MAX_LOG_SIZE', pow(1024, 2)); // 1Mb
const PARSEHUB_RUN_ATTEMPTS_MAX = 5;

$lastError = "";

?>