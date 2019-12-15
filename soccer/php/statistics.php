<?php
    require_once __DIR__ . '/logs.php';
    require_once __DIR__ . '/events.php';
    require_once __DIR__ . '/db/db_operations.php';

    if (isset($_POST['match_id'])) {
        $gameId = $_POST['match_id'];
        $old = loadLastGameStatistics($gameId);
        if (!$old) {
            $html = loadGameStatistics($id);
            $statistics = parseGameStatisticsHtml($html);
            insertStatistics($id, $statistics);
        } else {
            $statistics = $old;
        }
        echo json_encode($statistics);
     }

    function loadGameStatistics($id) {
        $httpcode = 0;
        
        for ($i = 0; $i < 3 && $httpcode != 200; $i++) {
            if ($i > 0) {
                sleep(2);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, DATA_URL . $id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $html = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }
        return $httpcode == 200 ? $html : "";
    }

    function parseGameStatisticsHtml($html) {
        $getNumber = function($n) {
            return is_numeric($n) ? intval($n) : false;
        };

        $doc = new DOMDocument();
        $ok = @$doc->loadHTML($html);
        if (!$ok) 
            return false;        
        $xpath = new DOMXPath($doc);
        $host = [];
        $guest = [];        
        $t1 = $xpath->query('//html/body/table[1]')->item(0);
        $t2 = $xpath->query('//html/body/table[2]/tr');

        $host['name']  = $xpath->query('tr[1]/td[1]/table[1]/tr[1]/td[1]', $t1)->item(0)->textContent;
        $handicap      = $xpath->query('tr[1]/td[2]/table[1]/tr[1]/td[1]/b/font', $t1)->item(0)->textContent;
        $guest['name'] = $xpath->query('tr[1]/td[3]/table[1]/tr[1]/td[1]', $t1)->item(0)->textContent;
        $hostGoals     = $xpath->query('tr[1]/td[2]/table[1]/tr[2]/td[1]', $t1)->item(0)->textContent;
        $guestGoals    = $xpath->query('tr[1]/td[2]/table[1]/tr[2]/td[3]', $t1)->item(0)->textContent;
        $host['gl']  = $getNumber($hostGoals);
        $guest['gl'] = $getNumber($guestGoals);

        foreach($t2 as $tr) {
            $tds = $xpath->query('td', $tr);
            if ($tds->length != 3) 
                continue;
            $h = str_replace("%", "", $tds->item(0)->textContent);
            $event = $tds->item(1)->textContent;
            $g = str_replace("%", "", $tds->item(2)->textContent);
            $key = event2code($event);
            if ($key) {
                $host[$key]  = $getNumber($h);
                $guest[$key] = $getNumber($g);
            }
        }
        return [
            'host'  => $host,
            'guest' => $guest,
            'handicap' => $handicap
        ];
    }

   // echo json_encode(parseData(file_get_contents(__DIR__ . '/xxx.html')));
?>