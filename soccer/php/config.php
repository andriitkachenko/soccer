<?php

const LOG_DIR = __DIR__ . '/../logs';

const DATA_FILE = LOG_DIR . '/last_game_list.json';
const LAST_PARSEHUB_RESPONSE_FILE = LOG_DIR . "/last_parsehub_response.txt";

const ACCESS_LOG = LOG_DIR . "/access.log";
const PARSEHUB_LOG = LOG_DIR . "/parsehub.log";
const CRON_LOG = LOG_DIR . "/cron.log";
const ERROR_LOG = LOG_DIR . "/error.log";

define('MAX_LOG_SIZE', 2 * pow(1024, 2)); // 2Mb

?>