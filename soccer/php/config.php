<?php

const DATA_FILE = __DIR__ . '/../logs/last_games.json';
const LAST_PARSEHUB_RESPONSE_FILE = __DIR__ . "/../logs/last_parsehub_response.txt";

const ACCESS_LOG = __DIR__ . "/../logs/access.log";
const ACCESS_LOG_ARCHIVE = __DIR__ . "/../logs/access_archive.log";
const PARSEHUB_LOG = __DIR__ . "/../logs/parsehub.log";
const PARSEHUB_LOG_ARCHIVE = __DIR__ . "/../logs/parsehub_archive.log";
const CRON_LOG = __DIR__ . "/../logs/cron.log";
const CRON_LOG_ARCHIVE = __DIR__ . "/../logs/cron_archive.log";
const DB_ERROR_LOG = __DIR__ . "/../logs/db_error.log";
const DB_ERROR_ARCHIVE = __DIR__ . "/../logs/db_error_archive.log";


const PARSEHUB_API_KEY = "tbXb7zgCH0L8";
const DATA_URL = "http://data.unogoal.life/detail.aspx?ID=";


define('MAX_LOG_SIZE', pow(1024, 2)); // 1Mb

$lastError = "";

?>