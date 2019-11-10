<?php
global $lastError;
require_once __DIR__ . '/db_operations.php';
require_once __DIR__ . '/../games.php';

//$games = json_decode(file_get_contents(DATA_FILE), true);
$games = readNotFinishedGames();
echo $games !== false ? json_encode($games) : $lastError;

?>
