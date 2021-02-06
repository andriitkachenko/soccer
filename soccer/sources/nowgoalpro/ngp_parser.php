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
const STATE_CANCELLED =  -10;
const STATE_PENDING =   -11;
const STATE_ABD =       -12;
const STATE_PAUSE =     -13;
const STATE_POSTPONE =  -14;
const STATE_AUTOFINISH = -15;

class NGPParser implements iParser {
    private static $logs = [];

    public static function getLog() {
        return implode(" - ", self::$logs);
    }
    
    public static function parseStat($html) {
        $stat = [];
        $doc = new DOMDocument();
        $ok = @$doc->loadHTML($html);
        if (!$ok) {
            return false;
        }
        $status = self::extractInitData($html);
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

    public static function parseGame($html) {
        self::$logs = [];
        $g = [];
        $doc = new DOMDocument();
        $ok = @$doc->loadHTML($html);
        if (!$ok) {
            self::$logs[] = 'Could not load html. ' . $html;
            return false;
        }
       
        $xpath = new DOMXpath($doc);
        // only item class
        //<div id=\"tb_1831305\" onclick=\"toAnalys(1831305)\" class=\"item \" data-mlid=\"15\">
        $nodes = $xpath->query("//div[contains(@class, 'item')]");
        if ($nodes->count() < 1) {
            self::$logs[] = 'No div with "item" class. ' . $html;
            return false;
        }
        $class = $nodes->item(0)->attributes->getNamedItem("class")->textContent;
        $class = str_replace(' ', '', trim($class));
        if (!in_array($class, ['item', 'hitem', 'itemh'])) {
            self::$logs[] = 'Not single "item" class. ' . $class;
            return false;
        }
        $id = $nodes->item(0)->attributes->getNamedItem("id")->textContent;
        $id = str_replace('tb_', '', trim($id));
        if (empty($id) || !ctype_digit($id)) {
            self::$logs[] = 'Could not find ID. ' . $nodes->item(0)->textContent;
            return false;
        }
        $g['id'] = @intval($id);
        // <div class="dayrow" data-day="2020_6_19">July 19. Sunday</div>
        /*
        $nodes = $xpath->query("//div[@data-day]");
        if ($nodes->count() < 1) {
            self::$logs[] = 'Could not find "data-day". '. $html;
            return false;
        }
        $date = trim($nodes->item(0)->attributes->getNamedItem("data-day")->textContent);
        */
        $date = date('Y_m_d');
        /*
        <div class="score" id="stat_1888573">
            <i id="state_1888573">80<i class="mit"><img src="/images/com/in.gif"></i></i>
            <span class="homeS" id="hsc_1888573">3</span>
            <span class="guestS" id="gsc_1888573">0</span>
        </div>
        */
        
        $nodes = $xpath->query("//div[@class='score'][@id='stat_$id']/i[@id='state_$id']");
        if (!$nodes->count()) {
            self::$logs[] = "'Could not find 'score' for ID $id. " . $html;
            return false;
        } 
        
        $min = strtolower(trim($nodes->item(0)->textContent));
        $state = 0;
        switch($min) {
            case 'ht' : $state = 2; break;
            case 'ot' : $state = 4; break;
            case 'pen' : $state = 5; break;
        }
        $extra = strpos($min, '+') !== false;

        $min = str_replace('+', '' , $min);
        $min = str_replace('ht', '45' , $min);
        $min = str_replace('ot', '91' , $min);
        $min = str_replace('pen', '120' , $min);
        if (!ctype_digit($min)) {
            self::$logs[] = 'Could not get game time. '. $nodes->item(0)->textContent;
            return false;
        }
        $min = intval($min);
        if ($min <= 45 && $state !== 2) {
            $state = 1;
        }
        if ($min > 45 && $min <= 90) {
            $state = 3;
        }
        $g['min'] = intval($min);
        $g['state'] = $state;
        $g['extra'] = (int)$extra;
        
        /// <span class="time" id="mt_1831305">10:00</span>
        $nodes = $xpath->query("//span[@class='time'][@id='mt_$id']");
        if ($nodes->count() < 1) {
            self::$logs[] = "Could not get start time for ID $id. " . $html;
            return false;
        }
        $time = trim($nodes->item(0)->textContent);
        $time = "$date-$time";
        $time = (DateTime::createFromFormat('Y_n_j-H:i', $time))->format('Y-m-d H:i:s');
        $g['start_time'] = $time;
        //<span href="/football/korea-league/league-15/" class="gameName leaRow" style="color:#990099">KOR D1</span>
        $nodes = $xpath->query("//span[contains(@class, 'gameName')][contains(@class, 'leaRow')]");
        if ($nodes->count() < 1) {
            self::$logs[] = "Could not get league. " . $html;
            return false;
        }
        $g['league_short'] = self::normalizeTitle($nodes->item(0)->textContent);
        $g['league_url'] = trim($nodes->item(0)->attributes->getNamedItem('href')->textContent);
        //<span id="ht_1831305" class="name">
        //     Suwon Samsung Bluewings
        // <font color=\"#880000\">(N)</font>
        //     <i>[9]</i>
        //     <i id="hR_1831305" class="redCard"></i>
        //     <i id="hY_1831305" class="yellowCard"><i>1</i></i>
        // </span>
        $tid = "ht_$id";
        $nodes = $xpath->query("//span[@id='$tid']/i[1]");
        if ($nodes->count() > 0) {
            $g['host_rank'] = self::normalizeTitle($nodes->item(0)->textContent, '[]');
        }
        $nodes = $xpath->query("//span[@id='$tid']/i");
        foreach($nodes as $ch) {
            $ch->parentNode->removeChild($ch);
        }
        $nodes = $xpath->query("//span[@id='$tid']");
        if ($nodes->count() < 1) {
            self::$logs[] = "Could not find host. " . $html;
            return false;
        }
        $g['host'] = self::normalizeTitle($nodes->item(0)->textContent, '()-');
        
        $tid = "gt_$id";
        $nodes = $xpath->query("//span[@id='$tid']/i[1]");
        if ($nodes->count() > 0) {
            $g['guest_rank'] = self::normalizeTitle($nodes->item(0)->textContent, '[]');
        }
        $nodes = $xpath->query("//span[@id='$tid']/i");
        foreach($nodes as $ch) {
            $ch->parentNode->removeChild($ch);
        }
        $nodes = $xpath->query("//span[@id='$tid']");
        if ($nodes->count() < 1) {
            self::$logs[] = "Could not find guest. " . $html;
            return false;
        }
        $g['guest'] = self::normalizeTitle($nodes->item(0)->textContent, '()-');
        $g['url'] = "/football/match/live-$id";
        return (object)$g;
    }

    private static function extractInitData($html) {
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

					<a onclick="closeBack(0)" class='back'></a>
					<div class="match-tools">
						<i class="setTop" id="btnOnTop" onclick="toggleFav()"></i>
						<i class="shareTop" onclick="toggleShare()"></i>
					</div>
					<div class="gameName">
						<a target="_blank" href="/football/australia-a-league/league-273/">Australia A-League</a>
					</div>
					<div class="date" id="liveMt">
						<script>
							document.write(timeToText(ToLocalTime('20200802173000'), 9))
						</script>
					</div>
					<div class="gameInfo">
						<div class="home">
							<div class="icon">
								<a href="/football/newcastle-jets/team-2915/"
									target="_self"><img src="//info.nowgoal.group/Image/team/images/20130923112423.png" alt="Newcastle Jets logo" /></a>
							</div>
							<span class="name"><a href="/football/newcastle-jets/team-2915/" target="_self">Newcastle Jets</a></span>
						</div>
						<div class="vs">
							<div id="liveFt" class="FT">VS</div>
							<div id="liveHt" class="HT">(1 - 0)</div>
							<div id="liveSt" class="status"></div>
						</div>
						<div class="guest">
							<div class="icon">
								<a href="/football/western-united-fc/team-24502/"
									target="_self"><img src="//info.nowgoal.group/Image/team/images/20191012082708.jpg" alt="Western United FC logo" /></a>
							</div>
							<span class="name"><a href="/football/western-united-fc/team-24502/" target="_self">Western United FC</a></span>
						</div>



					</div>


				</div>
         */

        $league = $xpath->query("//div[@class='match']//span[@class='tit']/a");
      
        $league = $league->count() ? self::parseTeamNode($xpath, $league->item(0)) : [];

        $host = $xpath->query(".//div[@class='gameInfo']/div[@class='home']/span[@class='name']/a", $node);
        $host = $host->count() ? self::parseTeamNode($xpath, $host->item(0)) : [];

        $guest = $xpath->query(".//div[@class='gameInfo']/div[@class='guest']/span[@class='name']/a", $node);
        $guest = $guest->count() ? self::parseTeamNode($xpath, $guest->item(0)) : [];

        return (object)[
            'league' => $league,
            'host' => $host,
            'guest' => $guest,
        ];
    }

    private static function parseTeamNode($xpath, $node) {
        $href = $node->attributes->getNamedItem('href');
        if (!empty($href)) {
            $url = trim($href->textContent);
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