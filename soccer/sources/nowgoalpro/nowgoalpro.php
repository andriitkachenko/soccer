<?php
declare(strict_types=1);

require_once __DIR__ . '/../../php/logs.php';
require_once __DIR__ . '/../../services/parsehub/parsehub.php';
require_once __DIR__ . '/ngp_parsehub_config.php';
require_once __DIR__ . '/ngp_parser.php';

interface iNowGoalPro {
    public function isParseHubClient() : bool;
    public function getParseHubGames($phData);
    public function runParseHubProject();
    public function runOneMinuteUpdate();
}

class NowGoalPro implements iNowGoalPro {

    public function updateNewGames($dbManager, $liveGames) {
        $liveGameIds = array_map(function($g) { return $g->id;}, $liveGames);
        // get new game IDs
        list($ok, $oldGameIds) = $dbManager->getExistingGameIds();
        if (!$ok) {
            return false;
        }
        $newGameIds = array_diff($liveGameIds, $oldGameIds);
        $goneGameIds = array_diff($oldGameIds, $liveGameIds);

        $newGames = array_filter($liveGames, function($liveGame) use($newGameIds) {
            return in_array($liveGame->id, $newGameIds);
        });
        $res = $dbManager->deleteNewGames($goneGameIds);
        if ($res === true) {
            $res = $dbManager->insertNewGames($newGames);
        }
        return $res;
    }
    
    public function isParseHubClient() : bool {
        return true;
    }

    public function runOneMinuteUpdate() {
        // read live games from DB
        // load stats for each live game
        // stop games which are not trackable - at min 20 there is no meaningful stat
        // save events
        // save overall game json
    }
    
    public function runParseHubProject() {
        $ph = new ParseHub(PH_PROJECT_TOKEN, PH_API_KEY);
        $res = $ph->runProject();
        //$ph->logRunProjectResult($res);
        return $res;
    }

    public function getParseHubGames($phData) {
        if (empty($phData->game) || !is_array($phData->game)) {
            return [];
        }
        $games = [];
        foreach($phData->game as $g) {
            /*
            {
                "id": "tb_1831305",
                "html": "<div id=\"tb_1831305\" onclick=\"toAnalys(1831305)\" class=\"item \" data-mlid=\"15\">\n        <div class=\"dayrow\" data-day=\"2020_6_19\">July 19. Sunday</div>\n        <div class=\"team \">\n            <div class=\"status\">\n                \n                <span class=\"time\" id=\"mt_1831305\">10:00</span>\n                <span href=\"/football/korea-league/league-15/\" class=\"gameName leaRow\" style=\"color:#990099\">KOR D1</span>\n            </div>\n            <div id=\"rht_1831305\" class=\"homeTeam\">\n                <span id=\"ht_1831305\" class=\"name\">\n                    \n                    Suwon Samsung Bluewings\n                    <i>[9]</i>\n                    <i id=\"hR_1831305\" class=\"redCard\"></i>\n                    <i id=\"hY_1831305\" class=\"yellowCard\"><i>1</i></i>\n                </span>\n            </div>\n            <div class=\"guestTeam\">\n                <span id=\"gt_1831305\" class=\"name\">\n                    \n                    Seongnam FC\n                    <i>[10]</i>\n                    <i id=\"gR_1831305\" class=\"redCard\"></i>\n                    <i id=\"gY_1831305\" class=\"yellowCard\"><i>1</i></i>\n                </span>\n            </div>\n        </div>\n        <div class=\"score\" id=\"stat_1831305\">\n            <i id=\"state_1831305\">\n                87<i class=\"mit\"><img src=\"/images/com/in.gif\"></i>\n            </i>\n            <span class=\"homeS\" id=\"hsc_1831305\">0</span>\n            <span class=\"guestS\" id=\"gsc_1831305\">1</span>\n        </div>\n        <div class=\"odds\">\n            <i>\n                <div id=\"hts_1831305\" class=\"HtScore\">\n                    HT 0-0\n                </div>\n                <div class=\"corner\">\n                    <i id=\"cn_1831305\" class=\"\"></i>\n                    <span id=\"corner_1831305\">3-4</span>\n                </div>\n                <div id=\"tImg_1831305\" class=\"setTop \" onclick=\"MarkTop(1831305,event,1)\"></div>\n            </i>\n            <div class=\"hOdds\">\n                <span id=\"o1_1831305\">0.70</span>\n                <span id=\"o2_1831305\">0</span>\n                <span id=\"o3_1831305\">1.21</span>\n            </div>\n            <div class=\"hOdds\">\n                <span id=\"o4_1831305\">2.00</span>\n                <span id=\"o5_1831305\">1.5</span>\n                <span id=\"o6_1831305\">0.38</span>\n            </div>\n        </div>\n        <br style=\"clear:both;\">\n        <div id=\"exList_1831305\" class=\"exbar\" style=\"display:none\">\n            \n        </div>\n    </div>"
            }
            */
            if (empty($g->id) || empty($g->html)) {
                continue;
            }
            $id = str_replace('tb_', '', $g->id);
            $game = NGPParser::parseGame($g->html);            
            if (!empty($game)) {
                $games[] = $game;
            }
        }
        return $games;
    }

    public function updateLiveGames($dbManager, $liveGames) {
        $liveGameIds = array_map(function($g) { return $g->id;}, $games);
        // get new game IDs
        list($ok, $existingGameIds) = $dbManager->getExistingGameIds($gamesId);
        if (!$ok) {
            return false;
        }
        $newGameIds = array_filter($gameIds, function($liveGameId) {return !in_array($liveGameId, $existingGameIds);});
        // get stat for each new game
        $data = [];
        foreach ($newGameIds as $i => $g) {
            if (!$i) {
                usleep(100000);
            }
            $html = file_get_contents(NGP_BASE_URL . $g['url']);
            if ($html === false) {
                continue;
            }
            $stat = NGPParser::parseGame($html);
            if (empty($stat)) {
                continue;
            }
            $data[] = [
                'game' => $g,
                'stat' => $stat
            ];
        }
        // save new games to the DB
        $ok = $dbManager->saveLiveGames($data);
        //
        return $ok;
    }

}


/*
$ngp = new NowGoalPro();
$gameData = json_decode(file_get_contents(__DIR__ . '/../../tests/ngp_live_games.json'));
print_r($ngp->getParseHubGames($gameData));
*/

/*
$ngp = new NowGoalPro();
$gameData = file_get_contents(__DIR__ . '/../../tests/ngp_parsed_games.json');
$ngp->updateGames($gameData);
*/

?>