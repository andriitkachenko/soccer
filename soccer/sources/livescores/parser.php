<?php
declare(strict_types = 1);

interface iParser {
    public static function parseGameList($html);
    public static function parseGameStat($html);
}

class Parser implements iParser {
    const FULL_TIME = 'FT';
    const HALF_TIME = 'HT';
    const MINUTE_SIGN = '&#x27;';


    public static function parseGameStat($html) {
        $errors = [];
        $doc = new DOMDocument();
        $ok = @$doc->loadHTML($html);
        if (!$ok) {
            return false;
        }
        $xpath = new DOMXpath($doc);
        $statNode = $xpath->query("//div[@data-id='stats'][@data-type='tab']");
        if ($statNode->count() != 1) {
            return [];
        }
        $statNodes = $xpath->query("div", $statNode->item(0));
        $stat = [];
        foreach($statNodes as $n) {
            $s = self::parseStatNode($xpath, $n);
            if (empty($s)) {
                continue;
            }
            if (empty($s['event']))  {
               $errors[] = "Unrecognized event: ". $n->$textContent;
               continue;     
            }
            $stat['host'][$s['event']] = $s['host']; 
            $stat['guest'][$s['event']] = $s['guest']; 
        }
        if (empty($stat)) {
            return [];
        }
        $gameNodes = $xpath->query("//body/div");
        if (!$gameNodes->count()) {
            return [];
        }
        $node = $gameNodes->item(0);
        $info = self::getGameData($xpath, $node, true);
        $stat = array_merge($stat, ['game' => $info]);
        return [
            'stat' => $stat,
            'errors' => $errors
        ];
    }

    public static function parseGameList($html) {
        $games = [];
        $doc = new DOMDocument();
        $ok = @$doc->loadHTML($html);
        if (!$ok) {
            return false;
        }
        $xpath = new DOMXpath($doc);
        $gameNodes = $xpath->query("//div[@data-eid]");
        if (!($gameNodes->count())) {
            return [];
        }
        $games =  [];
        foreach($gameNodes as $gameNode) {
            $data = self::getGameData($xpath, $gameNode);
            if (!empty($data['url'])) {
                $games[] = $data;
            }
        }
        return $games;
    }
    
    private static function getGameData($xpath, $gameNode, $forStat = false ) {
        /*
        <div class="row row-tall">
            <div class="min"> <img src="//cdn3.livescore.com/web/img/flash.gif" alt="live"/> 26&#x27; </div>
            <div class="ply tright" title="Mainz 05">Mainz 05</div>
            <div class="sco"> 0 - 2 </div>
            <div class="ply" title="RasenBallsport Leipzig">RasenBallsport Leipzig</div>
        </div>
        */
        /* 
			<div class="row-gray even" data-pid="6" data-eid="21893953" data-type="evt" data-esd="20200517160000">
				<div class="min"> 16:00 </div>
				<div class="ply tright name"> FC Ruh Brest </div>
				<div class="sco"> ? - ? </div>
				<div class="ply name"> FC Smolevichi </div>
			</div>        
        */
        /*
			<div class="row-gray " data-pid="6" data-eid="21893951" data-type="evt" data-esd="20200517140000">
				<div class="min"><img src="//cdn3.livescore.com/web/img/flash.gif" alt="live" /> 19&#x27; </div>
				<div class="ply tright name"> Dinamo Minsk </div>
				<div class="sco"> <a href="/soccer/belarus/premier/dinamo-minsk-vs-isloch/6-21893951/"
						class="scorelink">0 - 0</a> </div>
				<div class="ply name"> Isloch </div>
            </div>
        */
        if (!$forStat) {
            $id = trim($gameNode->attributes->getNamedItem('data-eid')->textContent);
            $id = ctype_digit($id) ? intval($id) : null;
            if (!$id) {
                return [];
            }
            $startTime = self::parseStartTime(trim($gameNode->attributes->getNamedItem('data-esd')->textContent));
            if (!$startTime) {
                return [];
            }
        }
        $players = $xpath->query("div[contains(@class, 'ply')]", $gameNode);
        if ($players->count() != 2) {
            return [];
        }
        $host = trim($players->item(0)->textContent);
        $guest = trim($players->item(1)->textContent);
        $time = $xpath->query("div[@class='min']", $gameNode);

        if ($time->count() != 1) {
            return [];
        }
        $time = self::parseGameTime($xpath, $time->item(0));
        $score = $xpath->query("div[@class='sco']", $gameNode);
        if ($score->count() != 1) {
            return [];
        } else {        
            $score = $score->item(0);
            $scores = explode('-', $score->textContent);
            if (count($scores) != 2 && !ctype_digit($scores[0]) && !ctype_digit($scores[1])) {
                $scores = [0, 0];
            }
            $url = $xpath->query("a[@class='scorelink']", $score);
            if ($url->count() != 1) {
                $url = "";
            } else {
                $url = trim($url->item(0)->attributes->getNamedItem('href')->textContent);
            }
        }
        $hostScore = intval($scores[0]);
        $guestScore = intval($scores[1]);
        $game = array_merge(
            $forStat ? [] : [
                'id' => $id,
                'url' => $url,
                'start' => $startTime // timestamp
            ],
            [
                'host' => $host,
                'guest' => $guest,
                'hostScore' => $hostScore,
                'guestScore' => $guestScore,
                'time' => $time
            ]
        );
        return $game;
    }
    private static function parseGameTime($xpath, $timeNode) {
        /*
            <div class="min"><img src="//cdn3.livescore.com/web/img/flash.gif" alt="live" /> HT </div>
            <div class="min"><img src="//cdn3.livescore.com/web/img/flash.gif" alt="live"/> 80&#x27; </div>
        */
        $time = trim($timeNode->textContent);
        $minSign = mb_convert_encoding(self::MINUTE_SIGN,'UTF-8','HTML-ENTITIES');
        $min = strpos($time, $minSign) !== false;

        $live = $xpath->query("img[@alt='live']", $timeNode)->count() == 1;
        $time = str_replace($minSign, '', $time);
  
        if ($min) {
            $mins = explode('+', $time);
            $min = !empty($mins[0]) && ctype_digit($mins[0]) ? intval($mins[0]) : 0;
            $extra= !empty($mins[1]) && ctype_digit($mins[1]) ? intval($mins[1]) : 0;
        } else {
            $min = $time == self::HALF_TIME ? 45 : 0;
            $extra = 0;
        }
        $notStarted = preg_match('/^\d{2}:\d{2}$/', $time);
        return [
            'time' => $time,
            'live' => $live && $min,
            'min' => $min,
            'extra' => $extra,
            'not_started' => $notStarted
        ];
    }
    private static function parseStartTime($str) {
        /*"20200517133000"*/
        if (strlen($str) != 14 || !ctype_digit($str)) {
            return false;
        }
        return mktime(
            intval(substr($str, 8, 2)),
            intval(substr($str, 10, 2)),
            intval(substr($str, 12, 2)),
            intval(substr($str, 4, 2)),
            intval(substr($str, 6, 2)),
            intval(substr($str, 0, 4))
        );
    }
    private static function parseStatNode($xpath, $node) {
        $divs = $xpath->query("div", $node);
        if (count($divs) != 5) {
            return [];
        }
        $event = trim($divs[2]->textContent);
        $event = self::event2Code(strtolower($event));
        if (empty($event)) {
            return [];
        }
        $host = trim($divs[0]->textContent);
        $guest = trim($divs[4]->textContent);
        if (!ctype_digit($host) || !ctype_digit($guest)) {
            return [];
        }
        $host = intval($host);
        $guest = intval($guest);
        return [
            'event' => $event,
            'host' => $host,
            'guest' => $guest
        ];
    }
    private static function event2Code($event) {
        switch($event) {
            case 'shots on target' :    $c = 'sg'; break;
            case 'shots off target' :   $c = 'sh'; break;
            case 'possession (%)' :     $c = 'bp'; break;
            case 'corners' :            $c = 'ck'; break;
            case 'offsides' :           $c = 'of'; break;
            case 'fouls' :              $c = 'fl'; break;
            case 'yellow cards' :       $c = 'yc'; break;
            case 'red cards' :          $c = 'rc'; break;
            case 'goal kicks' :         $c = 'gk'; break;
            case 'treatments' :         $c = 'tm'; break;
            default:                    $c = '';
        }
        return $c;
    }
}

?>