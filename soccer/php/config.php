<?php

const LOG_DIR = __DIR__ . '/../logs/';

const DATA_FILE = LOG_DIR . '/last_game_list.json';
const LAST_PARSEHUB_RESPONSE_FILE = LOG_DIR . "/last_parsehub_response.txt";

const LOG_FILES = [
    'access' =>   ['name' => 'access.log', 'size' => 2],
    'parsehub_run' => ['name' => 'parsehub_run.log', 'size' => 5],
    'parsehub_hook' => ['name' => 'parsehub_hook.log', 'size' => 5],
    'cron' =>     ['name' => 'cron.log', 'size' => 5],
    'error' =>    ['name' => 'error.log', 'size' => 2]
];

const ACCESS_LOG = LOG_FILES['access'];
const PARSEHUB_RUN_LOG = LOG_FILES['parsehub_run'];
const PARSEHUB_HOOK_LOG = LOG_FILES['parsehub_hook'];
const CRON_LOG = LOG_FILES['cron'];
const ERROR_LOG = LOG_FILES['error'];

?>