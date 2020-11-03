<?php

const DATA_FILE = __DIR__ . '/../logs/last_game_list.json';
const LAST_PARSEHUB_RESPONSE_FILE = __DIR__ . "/../logs/last_parsehub_response.txt";

const ACCESS_LOG = __DIR__ . "/../logs/access.log";
const PARSEHUB_LOG = __DIR__ . "/../logs/parsehub.log";
const CRON_LOG = __DIR__ . "/../logs/cron.log";
const ERROR_LOG = __DIR__ . "/../logs/error.log";

define('MAX_LOG_SIZE', 2 * pow(1024, 2)); // 2Mb

?>