<?php
declare(strict_types=1);

require_once __DIR__ . '/ngp_parser.php';
require_once __DIR__ . '/ngp_db_manager.php';
require_once __DIR__ . '/nowgoalpro.php';
require_once __DIR__ . '/../../services/db/db_connection.php';
require_once __DIR__ . '/../../services/db/db_settings.php';

const FILE_GAME = __DIR__ . '/../../tests/ngp_game.html';
const FILE_PARSEHUB_DATA = __DIR__ . '/../../tests/last_parsehub_response.txt';
const FILE_GAME_LIST = __DIR__ . '/../../tests/last_game_list.json';

function getParameter($name) {
    $p = isset($_POST[$name]) ? $_POST[$name] : null;
    return $p ? $p : (isset($_GET[$name]) ? $_GET[$name] : null);
}
$target = getParameter('target');
$operation = getParameter('operation');

switch($target) {
    case 'parser': 
        switch($operation) {
            case "game-parse" : 
                $source = null;
                $url = getParameter('url');
                $file = getParameter('file');
                $source = $url ? $url : FILE_GAME; 
                if ($source) {
                    $html = @file_get_contents($source);
                    if (empty($html)) {
                        echo humanizeBool(false);
                    } else {
                        print_r(NGPParser::parseStat($html));
                    }
                }
                break;
            case "game-update-file": 
                $url = getParameter('url');
                $ok = false;
                if ($url) {
                    $game =  @file_get_contents($url);
                    $ok = !empty($game) && file_put_contents(FILE_GAME, $game);
                }
                echo humanizeBool($ok);
                break;
            case "list-parse": 
                $data = json_decode(file_get_contents(FILE_PARSEHUB_DATA));
                if (isset($data->game)) {
                    print_r("Game count: " . count($data->game) . PHP_EOL);
                    foreach($data->game as $game) {
                        if (!empty($game->html)) {
                            print_r(NGPParser::parseGame($game->html));
                        }
                    }
                }
                break;
        }
        break;
    case "dbmanager" :
        $dbConn = new DbConnection(new DbSettings(true));
        $dbManager = new NgpDbManager($dbConn);
        $games = json_decode(file_get_contents(FILE_GAME_LIST));
        if (!$games) {
            echo "FAILED";
            die;
        }
        switch ($operation) {
            case "load-existing-games" : 
                $ids = array_map(function($g) { return $g->id;}, $games);
                $res = $dbManager->getExistingGameIds($ids);
                print_r($res);
                break;
            case "load-new-games" :
                list($ok, $res) = $dbManager->loadNewGames();
                print_r(humanizeBool($ok));
                print_r(PHP_EOL);
                print_r($res);
                break;
        }
        break;
    case "ngp" :
        $dbConn = new DbConnection(new DbSettings(true));
        $dbManager = new NgpDbManager($dbConn);
        $ngp = new NowGoalPro();
        $ngp->setDbManager($dbManager);
        $games = json_decode(file_get_contents(FILE_GAME_LIST));
        if (!$games) {
            echo "FAILED";
            die;
        }
        switch ($operation) {
            case "update-new-games" : 
                $res = $ngp->updateNewGames($games);
                echo humanizeBool($res);
                break;            
            case "update-games" : 
                $res = $ngp->updateGames($games);
                var_dump($res);
                break;
        }
        break;
}

?>