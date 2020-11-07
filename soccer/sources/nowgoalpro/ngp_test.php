<?php
declare(strict_types=1);

require_once __DIR__ . '/ngp_loader.php';
require_once __DIR__ . '/ngp_db_manager.php';
require_once __DIR__ . '/nowgoalpro.php';
require_once __DIR__ . '/../../services/db/db_connection.php';
require_once __DIR__ . '/../../services/db/db_settings.php';
require_once __DIR__ . '/../../php/utils.php';

const FILE_GAME = __DIR__ . '/../../tests/ngp_game.html';
const FILE_PARSEHUB_DATA = __DIR__ . '/../../tests/last_parsehub_response.txt';
const FILE_GAME_LIST = __DIR__ . '/../../tests/last_game_list.json';

$DEBUG_MODE = true;

function getParameter($name) {
    $p = isset($_POST[$name]) ? $_POST[$name] : null;
    return $p ? $p : (isset($_GET[$name]) ? $_GET[$name] : null);
}
$target = getParameter('target');
$operation = getParameter('operation');

function getGamesFromFile() {
    $file = FILE_GAME_LIST;
    $data = json_decode(file_get_contents($file), false);
    if (!$data) {
        echo "FAILED - could not get data from file " . $file;
        die;
    }
    return $data;    
}

function getResponseFromFile() {
    $file = FILE_PARSEHUB_DATA;
    $data = file_get_contents($file);
    if (!$data) {
        echo "FAILED - could not get data from file " . $file;
        die;
    }
    return $data;
}


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
                        echo humanizeBool(false) . ' - Empty html';
                    } else {
                        $res = NGPParser::parseStat($html);
                        print_r($res === false ?  humanizeBool($res) : $res);
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
                $data = json_decode(file_get_contents(FILE_PARSEHUB_DATA), false);
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
        $games = getGamesFromFile();
        switch ($operation) {
            case "load-existing-games" : 
                $ids = array_map(function($g) { return $g->id;}, $games);
                $res = $dbManager->getExistingGameIds($ids);
                $ok = $res !== false;
                print_r(humanizeBool($ok));                
                print_r(PHP_EOL);
                print_r($res);
                break;
            case "load-new-games" :
                $res = $dbManager->loadNewGames();
                $ok = $res !== false;
                logPrint($ok);
                break;
        }
        break;
    case "ngp" :
        $dbConn = new DbConnection(new DbSettings(true));
        $dbManager = new NgpDbManager($dbConn);
        $ngp = new NowGoalPro();
        $ngp->setDbManager($dbManager);
        switch ($operation) {
            case "update-new-games" : 
                $response = getResponseFromFile();
                $data = json_decode(str_replace("'", '', $response), false);
                $games = $ngp->getParseHubGames($data);
                $res = $ngp->updateNewGames($games);
                logPrint($res, $dbManager->getLastError());
                break;            
            case "1-update" : 
                $time = getParameter('time');
                $count = getParameter('count');
                $stopTime = is_null($time) ? null : (time() + intval($time));
                $count = is_null($count) ? null : intval($count);
                $res = $ngp->runOneMinuteUpdate($stopTime, $count);
                logPrint($res, $dbManager->getLastError());
                break;
        }
        break;
    case "loader" :
        $games = getGamesFromFile();
        $time = getParameter('time');
        $stopTime = is_null($time) ? null : (time() + intval($time));
        $loader = new NgpLoader($stopTime);
        switch ($operation) {
            case "load-games" : 
                $res = $loader->loadGames($games);
                print_r($res);
                break;            
        }
        break;
    case 'data' :
        $dbConn = new DbConnection(new DbSettings(true));
        $dbManager = new NgpDbManager($dbConn);
        switch ($operation) {
            case 'live-last-stats':
                $stats = $dbManager->loadLiveLastStats();
                print_r($stats);
                break;
        }
        break;
}

?>