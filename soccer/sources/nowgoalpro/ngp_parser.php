<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/parser/parser.php';

const STATE_PENALTY =   5;
const STATE_OVERTIME =  4;
const STATE_HALF2 =     3;
const STATE_HT =        2;
const STATE_HALF1 =     1;
const STATE_UNDEFINED = 0;
const STATE_FINISHED =  -1;
const STATE_CANCELLED = -10;
const STATE_PENDING =   -11;
const STATE_ABD =       -12;
const STATE_PAUSE =     -13;
const STATE_POSTPONE =  -14;
const STATE_AUTOFINISH = -15;

class NGPParser implements iParser {
    private static $logs = [];
    private static $html = "";

    public static function getLog() {
        $logs = self::$logs;
        if (!empty($logs) && !empty(self::$html)) {
            $logs[] = self::$html;
        }
        return implode(" - ", $logs);
    }
    
    public static function parseStat($html) {
        $stat = [];
        $doc = new DOMDocument();
        $ok = @$doc->loadHTML($html);
        if (!$ok) {
            return false;
        }
        $status = self::extractStatusData($html);
        if (!$status) {
            return false;
        }
        $xpath = new DOMXpath($doc);
        $nodes = $xpath->query("//div[@class='gameBox']");
        if ($nodes->count() < 1) {
            return false;
        }
        $gameNode = $nodes->item(0);
        $teams = self::parseGameNode($xpath, $gameNode);
        $stat = null;
        $nodes = $xpath->query("//h2[contains(normalize-space(), 'Match Stats')]/..//div[@class='barData']");
        if ($nodes->count()) {
            $statNode = $nodes->item(0);
            $stat = self::parseStatNode($xpath, $statNode);
        }
        if (!empty($stat)) {
            $stat->host = (object)array_merge((array)$stat->host, ['gl' => $status->hScore]);
            $stat->guest = (object)array_merge((array)$stat->guest, ['gl' => $status->gScore]);
        } else {
            $stat = (object)[
                'host' => (object)['gl' => $status->hScore], 
                'guest' => (object)['gl' => $status->gScore]
            ];
        }
        unset($status->hScore);
        unset($status->gScore);

        $min = empty($status->min) ? null : $status->min;
        $isTrackable = self::isTrackable($stat, $min);
        $isTrackTime = empty($min) || $min >= START_TRACKING_MINUTE;
        $status = addObjectProperty($status, 'trackable',!$isTrackable && !$isTrackTime ? null : (int)$isTrackable);

        return (object)[
            'id' => $status->game_id,
            'status' => $status,
            'league' => $teams->league, 
            'host' => $teams->host, 
            'guest' => $teams->guest, 
            'stat' => $stat
        ];
    }

/*
    <div id=\"tb_1971772\" onclick=\"toAnalys(1971772)\" class=\"item \" data-mlid=\"284\">
    <div class=\"dayrow\" data-day=\"2021_5_26\" data-mid=\"1971772\">2021/06/26(Saturday)</div>
    <div class=\"team \"><div class=\"status\">
    <span class=\"time\" id=\"mt_1971772\">05:00</span><span onclick=\"goTo('/football/database/league-284')\" class=\"gameName leaRow\" style=\"color:#ff6633\">JPN D2</span>\n            </div>
    <div id=\"rht_1971772\" class=\"homeTeam\">\<span id=\"ht_1971772\" class=\"name\">V-Varen Nagasaki</span><i>[5]</i>\<i id=\"hR_1971772\" class=\"redCard\"></i>\<i id=\"hY_1971772\" class=\"yellowCard\"><i>1</i></i>\n            </div>
    <div class=\"guestTeam\">\n                <span id=\"gt_1971772\" class=\"name\">Jubilo Iwata</span>\n                <i>[2]</i>\n                <i id=\"gR_1971772\" class=\"redCard\"></i>\n                <i id=\"gY_1971772\" class=\"yellowCard\"><i>1</i></i>\n            </div></div>
    <div class=\"timeScore\">\n            <i id=\"htit_1971772\">HT</i>\n            <div class=\"home\"><span class=\"odd \" id=\"hht_1971772\">0</span></div>
    <div class=\"guest\"><span class=\"odd \" id=\"ght_1971772\">0</span></div>\n        </div>\n        <div class=\"score\" id=\"stat_1971772\">\n            <i id=\"state_1971772\">\n                68<i class=\"mit\"><img src=\"/images/com/in_red.gif\"></i>\n            </i>\n            <span class=\"homeS\" id=\"hsc_1971772\">0</span>\n            <span class=\"guestS\" id=\"gsc_1971772\">0</span>\n        </div>
    <div class=\"odds\">\n            <i>\n                <div class=\"corner\">\n                    <i id=\"cn_1971772\" class=\"\"></i>\n                    <span id=\"corner_1971772\">3-4</span>\n                </div>\n                <div class=\"icon icon iconfont icon-font-lineup lineup\"></div>\n                \n                <div id=\"tImg_1971772\" class=\"icon iconfont icon-font-collect-off \" onclick=\"MarkTop(1971772,event,1)\"></div>
    </i><div class=\"hOdds lOdd\">\n                <span id=\"o1_1971772\">0.63</span>\n                <span id=\"o2_1971772\">0/-0.5</span>\n                <span id=\"o3_1971772\">1.40</span>\n            </div>\n            <div class=\"hOdds oOdd\">\n                <span id=\"o4_1971772\">0.91</span>\n                <span id=\"o5_1971772\">0.5</span>\n                <span id=\"o6_1971772\">0.99</span>\n            </div>\n        </div>
    <br style=\"clear:both;\">\n        <div id=\"exList_1971772\" class=\"exbar\" style=\"display:none\">\n            \n        </div>\n    </div>
*/

    public static function parseGame($html) {
        self::$logs = [];
        self::$html = $html;
        $g = [];
        $doc = new DOMDocument();
        $ok = @$doc->loadHTML($html);
        if (!$ok) {
            self::$logs[] = 'Could not load html';
            return false;
        }
        $xpath = new DOMXpath($doc);
        $game_node = self::get_game_node($xpath);
        if (!$game_node || !self::is_allowed_game_node($game_node)) {
            return false;
        }
        $id = self::get_game_id($game_node);
        if (!$id) {
            return false;
        }
        $time_node = self::get_time_node($xpath, $id);
        if (!$time_node) {
            return false;
        } 
        list($state, $min, $extra) = self::get_game_state_min_extra($time_node);
        if (!$min) {
            return false;
        }
        $start_time_node = self::get_start_time_node($xpath, $id);
        if (!$start_time_node) {
            return false;
        }
        $time = trim($start_time_node->textContent);
        $date = date('Y_m_d');        
        $start_time = (DateTime::createFromFormat('Y_n_j-H:i', "$date-$time"))->format('Y-m-d H:i:s');
        $league_node = self::get_league_node($xpath);
        if (!$league_node) {
            return false;
        }
        list($l_short, $l_url) = self::get_league_info($league_node);
        if (empty($l_short)) {
            return false;
        }
        $host_name = self::get_host_name($xpath, $id);
        if (!$host_name) {
            return false;
        }
        $host_rank = self::get_host_rank($xpath, $id);
        $guest_name = self::get_guest_name($xpath, $id);
        if (!$guest_name) {
            return false;
        }
        $guest_rank = self::get_guest_rank($xpath, $id);
        
        return (object)[
            'id' => $id,
            'min' => $min,
            'state' => $state,
            'extra' => $extra,
            'start_time' => $start_time,
            'league_short' => $l_short,
            'league_url' => $l_url,
            'host' => $host_name,
            'host_rank' => $host_rank,
            'guest' => $guest_name,
            'guest_rank' => $guest_rank,
            'url' => "/football/match/live-$id"
        ];
    }

    private static function get_game_id($game_node) {
        $id = $game_node->attributes->getNamedItem("id")->textContent;
        $id = str_replace('tb_', '', trim($id));
        if (empty($id) || !ctype_digit($id)) {
            self::$logs[] = 'Could not find ID. ' . $game_node->textContent;
            return null;
        }
        return @intval($id);
    }

    private static function is_allowed_game_node($game_node) {
        $running_game_classes = ['item', 'hitem', 'itemh'];
        $finished_game_classes = ['itemf', 'fitem'];
        if (!$game_node) {
            return false;
        }
        $class = $game_node->attributes->getNamedItem("class")->textContent;
        $class = str_replace(' ', '', trim($class));
        $is_running_game = in_array($class, $running_game_classes );
        $is_finished_game = in_array($class, $finished_game_classes);
        if (!$is_running_game) {
            if (!$is_finished_game) {
                self::$logs[] = 'Not supported class: ' . $class;
            }
            return false;
        }     
        return true;   
    }

    private static function get_game_node($xpath) {
        // only item class
        //<div id=\"tb_1831305\" onclick=\"toAnalys(1831305)\" class=\"item \" data-mlid=\"15\">
        return self::get_node($xpath, 
            "//div[contains(@class, 'item')]",
            "No div with 'item' class"
        );
    }

    private static function get_time_node($xpath, $id) {
        /*
        <div class="score" id="stat_1888573">
            <i id="state_1888573">80<i class="mit"><img src="/images/com/in.gif"></i></i>
            <span class="homeS" id="hsc_1888573">3</span>
            <span class="guestS" id="gsc_1888573">0</span>
        </div>
        */
        return self::get_node($xpath, 
            "//div[@class='score'][@id='stat_$id']/i[@id='state_$id']",
            "'Could not find time node for ID $id"
        );
    }

    private static function get_league_node($xpath) {
        //<span href="/football/korea-league/league-15/" class="gameName leaRow" style="color:#990099">KOR D1</span>
        return self::get_node($xpath, 
            "//span[contains(@class, 'gameName')][contains(@class, 'leaRow')]",
            "Could not get league"
        );
    }
    private static function get_league_info($league_node) {
        //<span onclick=\"goTo('/football/database/league-2245')\" class=\"gameName leaRow\" style=\"color:#53ac98\">PCB</span>        
        $short = self::normalizeTitle($league_node->textContent);
        $onclick = trim($league_node->attributes->getNamedItem('onclick')->textContent);
        $url = str_replace(["goTo('", "')"], '', $onclick);
        if (empty($short)) {
            self::$logs[] = "Could not get league info";
            $short = null;
        }
        return [$short, $url];
    }

    private static function get_start_time_node($xpath, $id) {
        /// <span class="time" id="mt_1831305">10:00</span>
        return self::get_node($xpath, 
            "//span[@class='time'][@id='mt_$id']",
            "Could not get start time for ID $id"
        );
    }

    private static function get_game_state_min_extra($time_node) {
        $min = strtolower(trim($time_node->textContent));
        $state = 0;
        switch($min) {
            case 'ft' : $state = STATE_FINISHED; break;
            case 'ht' : $state = STATE_HT; break;
            case 'ot' : $state = STATE_OVERTIME; break;
            case 'pen' : $state = STATE_PENALTY; break;
        }
        $extra = strpos($min, '+') !== false;
        
        $min = str_replace('+', '' , $min);
        $min = str_replace('ht', '45' , $min);
        $min = str_replace('ft', '90' , $min);
        $min = str_replace('ot', '91' , $min);
        $min = str_replace('pen', '120' , $min);
        $min = ctype_digit($min) ? intval($min) : null;

        if (!$min) {
            self::$logs[] = 'Could not get game time. '. $time_node->textContent;
        } else if ($min && $min <= 45 && $state !== STATE_HT) {
            $state = STATE_HALF1;
        } else if ($min && $min > 45 && $min <= 90 && $state !== STATE_FINISHED) {
            $state = STATE_HALF2;
        }

        return [$state, $min, (int)$extra];
    }

    private static function get_node($xpath, $query, $error_message) {
        $nodes = $xpath->query($query);
        if (!($nodes->count())) {
            self::$logs[] = $error_message;
            return null;
        }
        return $nodes->item(0);
    }

    private static function get_host_name($xpath, $id) {
        return self::get_team_name($xpath, $id, true);
    }

    private static function get_guest_name($xpath, $id) {
        return self::get_team_name($xpath, $id, false);
    }

    private static function get_team_name($xpath, $id, $host) {
        /*
            <div id=\"rht_1971772\" class=\"homeTeam\">
                <span id=\"ht_1971772\" class=\"name\">V-Varen Nagasaki</span>
                <i>[5]</i>
                <i id=\"hR_1971772\" class=\"redCard\"></i>
                <i id=\"hY_1971772\" class=\"yellowCard\"><i>1</i></i>
            </div>        
        */        
        $i = $host ? 'h' : 'g';
        $t = $host ? 'host' : 'guest';
        $team_node = self::get_node($xpath, 
            "//span[@id='${i}t_$id'][contains(@class, 'name')]",
            "Could not find $t"
        );
        if (!$team_node) {
            return null;        
        }
        return self::normalizeTitle($team_node->textContent, '()-');
    }

    private static function get_host_rank($xpath, $id) {
        return self::get_team_rank($xpath, $id, true);
    }

    private static function get_guest_rank($xpath, $id) {
        return self::get_team_rank($xpath, $id, false);
    }

    private static function get_team_rank($xpath, $id, $host) {
        /*
            <div id=\"rht_1971772\" class=\"homeTeam\">
                <span id=\"ht_1971772\" class=\"name\">V-Varen Nagasaki</span>
                <i>[5]</i>
                <i id=\"hR_1971772\" class=\"redCard\"></i>
                <i id=\"hY_1971772\" class=\"yellowCard\"><i>1</i></i>
            </div>        
        */
        $class = $host ? 'homeTeam' : 'guestTeam';
        $nodes = $xpath->query("//div[@class='$class']/i[1]");
        if ($nodes->count() > 0) {
            return self::normalizeTitle($nodes->item(0)->textContent, '[]');
        }
        return null;
    }

    private static function extractStatusData($html) {
        //mslive.init("1887298^3^20200808190000^20200808200335^2^2^1^2^0^0^0^0^^^^^");
        $ok = preg_match('/mslive.init\("(.+)"\);/', $html, $matches);
        if (!$ok || empty($matches[1])) {
            return false;
        }
        $inits = explode('^', $matches[1]);
        $data = [];

        $state = !empty($inits[1]) ? intval($inits[1]) : STATE_UNDEFINED;
        if ($state == STATE_UNDEFINED) {
            return false;
        }
        $data['state'] = $state;
        $data['state-txt'] = self::state2string($state);
        if (empty($inits[0]) || !ctype_digit($inits[0])) {
            return false;
        }
        $data['game_id'] = intval($inits[0]);
        
        $data['live'] = (int)($state > 0);
        if (!empty($inits[2])) {
            $t = (DateTime::createFromFormat("YmdHis", $inits[2]))->getTimestamp() - (8 * 3600);
            $data['start_time'] = $t;
        }
        if ($data['live'] && !empty($inits[3])) {
            $realStartTime = (DateTime::createFromFormat("YmdHis", $inits[3]))->getTimestamp() - (8 * 3600);
            $data['start_real'] = $realStartTime;
            switch ($state) {
                case STATE_HALF1 :
                    $data['min'] = floor((time() - $realStartTime) / 60.);
                    break;
                case STATE_HT :
                    $data['min'] = 45;
                    break;
                case STATE_HALF2 :
                case STATE_OVERTIME :
                    $data['min'] = floor((time() - $realStartTime) / 60.) + 45;
                    break;
                case STATE_PENALTY :
                    $data['min'] = 120;
                    break;
            }
        } 
        
        if (($data['live'] && !isset($data['min'])) || !isset($inits[4]) || !ctype_digit($inits[4]) || !isset($inits[5]) || !ctype_digit($inits[5])) {
            return false;
        }
        
        $data['hScore'] = intval($inits[4]);
        $data['gScore'] = intval($inits[5]);

        return (object)$data;
    }

    private static function state2string($state) {
        $s = 'Undefined';
        switch($state) {
            case STATE_PENALTY :    $s = 'Penalty'; break;
            case STATE_OVERTIME :   $s = 'Overtime'; break;
            case STATE_HALF2 :      $s = 'Half 2'; break;
            case STATE_HT :         $s = 'HT'; break;
            case STATE_HALF1 :      $s = 'Half 1'; break;;
            case STATE_FINISHED :   $s = 'Finished'; break;
            case STATE_CANCELLED :  $s = 'Cancelled'; break;
            case STATE_PENDING :    $s = 'Pending'; break;
            case STATE_ABD :        $s = 'Abd.'; break;
            case STATE_PAUSE :      $s = 'Pause'; break;
            case STATE_POSTPONE :   $s = 'Postpone'; break;
            case STATE_UNDEFINED :
            default: break;
        }
        return $s;
    }

     private static function parseGameNode($xpath, $node) {
/*
    <div class="gameBox">
        <div class="header">
            
<a onclick="closeBack(0)" class='back'></a>
            <div class="gameName">
                <span target="_self" onclick="goTo('/football/database/league-1')">Ireland Premier Division</span>
                 <span class="date" id="liveMt">
                    <script>document.write(timeToText(ToLocalTime('20210612024500'), 9))</script>
                </span>
            </div>
            <span class="icon iconfont icon-font-class"></span>
            <div id="miniPop" class="selectPopup minipopup" style="display:none;">
                <div class="item">
                    <span class="icon iconfont icon-font-share"></span>Share
                </div>
                <div class="item">
                    <span class="icon iconfont icon-font-collect-off"></span>Favorite
                </div>
            </div>
            
        </div>

        <div class="gameInfo">
            <div class="home">
                <div class="icon">
                    <span onclick="goTo('/football/team/1345')" target="_self">
                        <img src="//info.nowgoal3.com/Image/team/images/20130408191151.png" alt="Dundalk logo" />
                    </span>
                </div>
                <span class="name">
                    <span onclick="goTo('/football/team/1345')" target="_self">Dundalk</span>
                </span>
            </div>
            <div class="vs">
               
                <span id="liveSt" class="status">Part2</span>
                <div id="liveFt" class="FT">VS</div>
                <div id="liveHt" class="HT">(1 - 1)</div>
            </div>
            <div class="guest">
                <div class="icon">
                    <span onclick="goTo('/football/team/317')" target="_self">
                        <img src="//info.nowgoal3.com/Image/team/images/20130408193035.png" alt="Waterford United logo" />
                    </span>
                </div>
                <span class="name">
                    <span onclick="goTo('/football/team/317')" target="_self">Waterford United</span>
                </span>
            </div>
        </div>
    </div>
*/

$league = $xpath->query("//div[@class='match']//div[@class='gameName']/a");
      
        $league = $league->count() ? self::parseTeamNode($xpath, $league->item(0)) : [];

        $host = $xpath->query(".//div[@class='gameInfo']/div[@class='home']/span[@class='name']/span", $node);
        $host = $host->count() ? self::parseTeamNode($xpath, $host->item(0)) : [];

        $guest = $xpath->query(".//div[@class='gameInfo']/div[@class='guest']/span[@class='name']/span", $node);
        $guest = $guest->count() ? self::parseTeamNode($xpath, $guest->item(0)) : [];

        return (object)[
            'league' => $league,
            'host' => $host,
            'guest' => $guest,
        ];
    }

    private static function parseTeamNode($xpath, $node) {
/*
<span onclick="goTo('/football/team/1345')" target="_self">Dundalk</span>
<span target="_self" onclick="goTo('/football/database/league-1')">Ireland Premier Division</span>
<span onclick="goTo('/football/team/317')" target="_self">Waterford United</span>
*/

        if (!$node) {
            return (object)[];
        }
        $onclick = trim($node->attributes->getNamedItem('onclick')->textContent);
        if (!empty($onclick)) {
            $url = str_replace(["goTo('", "')"], '', $onclick);
            $id = preg_match('/(\d+)$/', $url, $matches);
        }
        $title = self::normalizeTitle($node->textContent, '()-');
        return (object)array_merge(
            empty($url)        ? [] : ['url' => $url],
            empty($title)      ? [] : ['title' => $title],
            empty($matches[1]) ? [] : ['id' => intval($matches[1])]
        );
    }

    private static function parseStatNode($xpath, $node) {
        $stat = [];
        $nodes = $xpath->query(".//div[@class='item']", $node);
        foreach($nodes as $n) {
            $item = self::parseStatItem($xpath, $n);
            if (!empty($item)) {
                $stat['host'][$item->event] = $item->h;
                $stat['guest'][$item->event] = $item->g;
            }
        }        
        return (object)$stat;
    }

    private static function parseStatItem($xpath, $node) {
        $title = $xpath->query(".//div[@class='tit']", $node);
        if (!$title->count()) {
            return false;
        }
        $title = trim($title->item(0)->textContent);
        $event = self::event2code($title);
        if (empty($event)) 
            return false;

        $host = $xpath->query(".//div[@class='home']", $node);
        $guest = $xpath->query(".//div[@class='guest']", $node);
        if (!$host->count() || !$guest->count()) { 
            return false;
        }
        $host = str_replace('%', '', trim($host->item(0)->textContent));
        $guest = str_replace('%', '', trim($guest->item(0)->textContent));

        if (!ctype_digit($host) || !ctype_digit($guest)) {
            return false;
        }

        return (object)[
            'event' => $event,
            'h' => intval($host),
            'g' => intval($guest),
        ];
    }

    private static function normalizeTitle($str) {
        return preg_replace('/[^a-zA-Z1-9- \(\)\[\]]/', '', trim($str));
    }

    private static function normalizeURL($url) {
        return preg_replace('/[^a-z1-9-]/', '', trim($url));
    }

    private static function event2Code($event) {
        switch(strtolower($event)) {
            case 'shots on goal' :      $c = 'sg'; break;
            case 'shots' :              $c = 'sh'; break;
            case 'attack' :             $c = 'at'; break;
            case 'dangerous attack' :   $c = 'da'; break;
            case 'red cards' :          $c = 'rc'; break;
            case 'yellow cards' :       $c = 'yc'; break;
            case 'possession' :         $c = 'bp'; break;
            case 'corner kicks' :       $c = 'ck'; break;
            case 'fouls' :              $c = 'fl'; break;
            case 'offsides' :           $c = 'of'; break;
            default:                    $c = '';
        }
        return $c;
    }
    
    private static function isTrackable($stat, $min) {
        $h = $stat->host;
        $g = $stat->guest;
        $isShots = isset($h->sh) || isset($g->sh);
        $isAttacks = isset($h->at) || isset($g->at);
        $isDangerousAttacks = isset($h->da) || isset($g->da);
        $isBallPossession = isset($h->bp) || isset($g->bp);
        $noShotMinute = empty($min) || $min < MAX_NO_SHOT_MINUTE;

        return 
            $isShots 
            || (
                $noShotMinute 
                && (
                    $isAttacks 
                    || $isDangerousAttacks 
                    || $isBallPossession
                ));
    }    
}

?>