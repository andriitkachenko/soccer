<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/cron/livescores/parser.php';

$test = $_POST['test'];

switch($test) {
    case 'stat' :
        $html = file_get_contents('livescore_game_example.html');
        $stat = Parser::parseGameStat($html);
        print_r($stat);
        break;
    case 'list' :
        $html = file_get_contents('livescore_list_example.html');
        $list = Parser::parseGameList($html);
        print_r($list);
        break;
    default : 'Unknown test name';
}

?>