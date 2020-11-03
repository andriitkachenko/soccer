<?php

require_once __DIR__ . '/../../php/utils.php';
require_once __DIR__ . '/../../php/curl.php';
require_once __DIR__ . '/ngp_parser.php';
require_once __DIR__ . '/ngp_config.php';

const LOAD_GAME_CHUNK = 15;

interface iLoader {
    public function loadGameStat($game);
    public function loadMultiGameStats($games, $stopTime);
}

class NgpLoader implements iLoader {

    public function loadGameStat($game) {
        return $this->getGameStat($game);
    }

    public function loadMultiGameStats($games, $stopTime) {
        if (empty($games) || !is_array($games)) {
            return [];
        }
      
        $chunks = array_chunk($games, LOAD_GAME_CHUNK, true);
        $stats = [];
        foreach($chunks as $chunk) {
            if ($stopTime && $stopTime < time()) {
                break;
            }
            $htmls = $this->multiGameLoad($chunk, $stopTime);
            foreach($chunk as $g) {
                $s = null;
                $id = $g->id;
                if (!empty($htmls[$id])) {
                    $stat = NGPParser::parseStat($htmls[$id]);
                    if (!empty($stat) && !empty($g->url)) {
                        $updatedStat = addObjectProperty($stat, 'url', $g->url);
                        if ($updatedStat) { 
                            $s = $updatedStat; 
                        }
                    }
                }
                $stats[$id] = $s;
            }
        }
        return $stats; // [ id => stat nullable ]
    }

    private function multiGameLoad($games, $stopTime) {
        if(empty($games)) {
            return [];
        }
        $urls = [];
        foreach($games as $g) {
            $urls[$g->id] = NGP_BASE_URL . $g->url;
        }
        return curlMultiGet($urls, $stopTime);
    }

    private function getGameStat($game) {
        if (empty($game->url)) {
            return false;
        }
        $html = curlGet(NGP_BASE_URL . $game->url);
        if (empty($html)) {
            return false;
        }
        $stat = NGPParser::parseStat($html);
        if ($stat === false) {
            return false;
        }
        return $stat;
    }
}    
?>
