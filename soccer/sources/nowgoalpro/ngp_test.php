<?php
declare(strict_types=1);

require_once __DIR__ . '/ngp_parser.php';

const FILE_STAT = __DIR__ . '/../../tests/ngp_game.html';
const FILE_GAME_LIST = __DIR__ . '/../../tests/ngp_live_games.json';

function getParameter($name) {
    $p = isset($_POST[$name]) ? $_POST[$name] : null;
    return $p ? $p : (isset($_GET[$name]) ? $_GET[$name] : null);
}
$target = getParameter('target');
$operation = getParameter('operation');

switch($target) {
    case 'game': 
        switch($operation) {
            case "parse" : 
                $source = null;
                $url = getParameter('url');
                $file = getParameter('file');
                $source = $url ? $url : ($file ? FILE_STAT : null); 
                if ($source) {
                    $html = file_get_contents($source);
                    print_r(NGPParser::parseStat($html));
                }
                break;
            case "update-file": 
                $url = getParameter('url');
                if ($url) {
                    $ok = file_put_contents(FILE_STAT, file_get_contents($url));
                }
                echo !empty($ok) ? 'OK' : 'Failed';
                break;
        }
        break;
    case "list": 
        $data = json_decode(file_get_contents(FILE_GAME_LIST));
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

?>