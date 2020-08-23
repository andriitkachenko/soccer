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

class NGPParser implements iParser {
    
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
        $stat = [];
        $nodes = $xpath->query("//h2[contains(normalize-space(), 'Match Stats')]/..//div[@class='barData']");
        if ($nodes->count()) {
            $statNode = $nodes->item(0);
            $stat = self::parseStatNode($xpath, $statNode);
            $stat['h-stat']['gl'] = $status['h-gl'];
            $stat['g-stat']['gl'] = $status['g-gl'];
        }
/*        
        $events = [];
        $nodes = $xpath->query("//div[@id='eventDiv']//div[@class='eventBox']");
        if ($nodes->count()) {
            $eventBox = $nodes->item(0);
            $events = self::parseEventNode($xpath, $eventBox);
        }
*/
        return [
            'status' => $status,
            'teams' => $teams, 
            'stat' => $stat
        ];
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
        $data['id'] = intval($inits[0]);
        
        $data['live'] = $state > 0 ? $state : 0;
        if ($state == STATE_HALF1 && !empty($inits[2])) {
            $timeHalf1 = (DateTime::createFromFormat("YmdHis", $inits[2]))->getTimestamp() - (8 * 3600);
            $data['min'] = floor((time() - $timeHalf1) / 60.);
        }
        if ($state == STATE_HALF2 && !empty($inits[3])) {
            $timeHalf2 = (DateTime::createFromFormat("YmdHis", $inits[3]))->getTimestamp() - (8 * 3600);
            $data['min'] = floor((time() - $timeHalf2) / 60.) + 45;
        }

        if (!isset($inits[4]) || !ctype_digit($inits[4]) || !isset($inits[5]) || !ctype_digit($inits[5])) {
            return false;
        }
        $data['h-gl'] = intval($inits[4]);
        $data['g-gl'] = intval($inits[5]);

        return $data;
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

        $league = $xpath->query(".//div[@class='gameName']/a", $node);
        $league = $league->count() ? self::parseTitleNode($xpath, $league->item(0)) : [];

        $host = $xpath->query(".//div[@class='gameInfo']/div[@class='home']/span[@class='name']/a", $node);
        $host = $host->count() ? self::parseTitleNode($xpath, $host->item(0)) : [];

        $guest = $xpath->query(".//div[@class='gameInfo']/div[@class='guest']/span[@class='name']/a", $node);
        $guest = $guest->count() ? self::parseTitleNode($xpath, $guest->item(0)) : [];

        return [
            'league' => $league,
            'host' => $host,
            'guest' => $guest,
        ];
    }

    private static function parseTitleNode($xpath, $node) {
        $href = $node->attributes->getNamedItem('href');
        if (!empty($href)) {
            $url = trim($href->textContent);
            $id = preg_match('/(\d+)\/$/', $url, $matches);
        }
        $title = trim($node->textContent);
        return array_merge(
            empty($url)        ? [] : ['url' => $url],
            empty($title)      ? [] : ['title' => $title],
            empty($matches[1]) ? [] : ['id' => intval($matches[1])]
        );
    }

    private static function parseEventNode($xpath, $node) {
        $stat = [];
        $nodes = $xpath->query(".//div[@class='eventIcon']", $node);
        if (!$nodes->count()) {
            return [];
        }
        $names = array_map(
            function($n) { return trim($n);},
            explode(html_entity_decode('&nbsp;'),$nodes->item(0)->textContent)
        );
        $eventCodes = [];
        foreach($names as $n) {
            $eventCodes[] = self::eventName2code($n);
        }
        $nodes = $xpath->query(".//div[@class='eventIcon']/img", $node);
        if (!$nodes || !$nodes->count()) {
            return [];
        }
        $eventImages = [];
        if ($nodes->count() !=  count($eventCodes)) {
            return [];
        }
        for($i = 0; $i < $nodes->count(); $i++) {
            $img = $nodes->item($i);
            $eventImages[$eventCodes[$i]] = trim($img->attributes->getNamedItem("src")->textContent);
        }
        $hostScore = $xpath->query(
            ".//div[@class='eventList']//div[@class='item']//div[@class='homeEvent']//img[@src='" . $eventImages['gl'] . "']
            | .//div[@class='eventList']//div[@class='item']//div[@class='homeEvent']//img[@src='" . $eventImages['pn'] . "']
            | .//div[@class='eventList']//div[@class='item']//div[@class='homeEvent']//img[@src='" . $eventImages['ag'] . "']",
            $node
        );
        $guestScore = $xpath->query(
            ".//div[@class='eventList']//div[@class='item']//div[@class='guestEvent']//img[@src='" . $eventImages['gl'] . "']
            | .//div[@class='eventList']//div[@class='item']//div[@class='homeEvent']//img[@src='" . $eventImages['pn'] . "']
            | .//div[@class='eventList']//div[@class='item']//div[@class='homeEvent']//img[@src='" . $eventImages['ag'] . "']",
            $node
        );
        $stat['h_gl'] = $hostScore->count();
        $stat['g_gl'] = $guestScore->count();
        return $stat;
    }

    private static function eventName2code($name) {
        switch(strtolower($name)) {
            case 'goals': return 'gl';
            case 'pen': return 'pn';
            case 'o.g': return 'ag';
            case '2y to r': return 'rc';
            case 'subs.': return 'sb';
            default : return '';
        }
    }

    private static function parseEventItem($xpath, $node) {
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

        return [
            'event' => $event,
            'h' => intval($host),
            'g' => intval($guest),
        ];
    }

    private static function parseStatNode($xpath, $node) {
        $stat = [];
        $nodes = $xpath->query(".//div[@class='item']", $node);
        foreach($nodes as $n) {
            $item = self::parseStatItem($xpath, $n);
            if (!empty($item)) {
                $stat['h-stat'][$item['event']] = $item['h'] ;
                $stat['g-stat'][$item['event']] = $item['g'] ;
            }
        }        
        return $stat;
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

        return [
            'event' => $event,
            'h' => intval($host),
            'g' => intval($guest),
        ];
    }

    public static function parseGame($html) {
   
        $g = [];
        $doc = new DOMDocument();
        $ok = @$doc->loadHTML($html);
        if (!$ok) {
            return false;
        }
        $xpath = new DOMXpath($doc);
        // only item class
        //<div id=\"tb_1831305\" onclick=\"toAnalys(1831305)\" class=\"item \" data-mlid=\"15\">
        $nodes = $xpath->query("//div[contains(@class, 'item')]");
        if ($nodes->count() < 1) {
            return false;
        }
        $class = $nodes->item(0)->attributes->getNamedItem("class")->textContent;
        if (trim($class) != 'item') {
            return false;
        }
        $id = $nodes->item(0)->attributes->getNamedItem("id")->textContent;
        $id = str_replace('tb_', '', trim($id));
        if (empty($id) || !ctype_digit($id)) {
            return false;
        }
        $g['id'] = @intval($id);
        // <div class="dayrow" data-day="2020_6_19">July 19. Sunday</div>
        $nodes = $xpath->query("//div[@data-day]");
        if ($nodes->count() < 1) {
            return false;
        }
        $date = trim($nodes->item(0)->attributes->getNamedItem("data-day")->textContent);
        /*
        <div class="score" id="stat_1888573">
            <i id="state_1888573">80<i class="mit"><img src="/images/com/in.gif"></i></i>
            <span class="homeS" id="hsc_1888573">3</span>
            <span class="guestS" id="gsc_1888573">0</span>
        </div>
        */
        
        $nodes = $xpath->query("//div[@class='score'][@id='stat_$id']/i[@id='state_$id']");
        if (!$nodes->count()) {
            return false;
        } 
        $min = trim($nodes->item(0)->textContent);
        if (!ctype_digit($min)) {
            return false;
        }
        $g['min'] = intval($min);
        
        /// <span class="time" id="mt_1831305">10:00</span>
        $nodes = $xpath->query("//span[@class='time'][@id='mt_$id']");
        if ($nodes->count() < 1) {
            return false;
        }
        $time = trim($nodes->item(0)->textContent);
        $time = "$date-$time";
        $time = (DateTime::createFromFormat('Y_n_j-H:i', $time))->format('Y-m-d H:i:s');
        $g['start_time'] = $time;
        //<span href="/football/korea-league/league-15/" class="gameName leaRow" style="color:#990099">KOR D1</span>
        $nodes = $xpath->query("//span[contains(@class, 'gameName')][contains(@class, 'leaRow')]");
        if ($nodes->count() < 1) {
            return false;
        }
        $g['league_short'] = trim($nodes->item(0)->textContent);
        $g['league_href'] = trim($nodes->item(0)->attributes->getNamedItem('href')->textContent);
        //<span id="ht_1831305" class="name">
        //     Suwon Samsung Bluewings
        // <font color=\"#880000\">(N)</font>
        //     <i>[9]</i>
        //     <i id="hR_1831305" class="redCard"></i>
        //     <i id="hY_1831305" class="yellowCard"><i>1</i></i>
        // </span>
        $tid = "ht_$id";
        $nodes = $xpath->query("//span[@id='$tid']/i[1]");
        if ($nodes->count() < 1) {
            return false;
        }
        $g['host_rank'] = trim($nodes->item(0)->textContent);
        $nodes = $xpath->query("//span[@id='$tid']/i");
        foreach($nodes as $ch) {
            $ch->parentNode->removeChild($ch);
        }
        $nodes = $xpath->query("//span[@id='$tid']");
        if ($nodes->count() < 1) {
            return false;
        }
        $g['host'] = trim($nodes->item(0)->textContent);
        
        $tid = "gt_$id";
        $nodes = $xpath->query("//span[@id='$tid']/i[1]");
        if ($nodes->count() < 1) {
            return false;
        }
        $g['guest_rank'] = trim($nodes->item(0)->textContent);
        $nodes = $xpath->query("//span[@id='$tid']/i");
        foreach($nodes as $ch) {
            $ch->parentNode->removeChild($ch);
        }
        $nodes = $xpath->query("//span[@id='$tid']");
        if ($nodes->count() < 1) {
            return false;
        }
        $g['guest'] = trim($nodes->item(0)->textContent);
        $gameUrlTitle = strtolower($g['host'] ) . ' vs ' . strtolower($g['guest']);
        $gameUrlTitle = str_replace('(n)', '', $gameUrlTitle);
        $gameUrlTitle = str_replace(' ', '-', $gameUrlTitle);
        $g['url'] = "/football-match/$gameUrlTitle/live-$id/";
        return $g;
    }

    private static function event2Code($event) {
        switch(strtolower($event)) {
            case 'shots on goal' :      $c = 'sh'; break;
            case 'shots' :              $c = 'sg'; break;
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
}

?>