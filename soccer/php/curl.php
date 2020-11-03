<?php
declare(strict_types = 1);

require_once __DIR__ . '/logs.php';

const RESPONSE_EXPECTED_TIME = 0.5; // seconds per request

function curlGet($url) {
    $httpcode = 0;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36"
    ]);
    $html = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if (curl_errno($curl)) {
        errorLog(['curlGet', $url, curl_error($curl)]);
    }    
    curl_close($curl);
    return $httpcode == 200 ? $html : "";
}

function curlMultiGet($urls, $stopTime = null) {
    if (!is_array($urls)) return false;
    if (empty($urls)) return [];

    $sleepTime = (int)ceil(count($urls) * RESPONSE_EXPECTED_TIME);
    $timeLeft = $stopTime ?  $stopTime - time() : null;
    $maxTime = (int)floor(MAX_PROCESSING_TIME * 0.9);

    if ($timeLeft && $timeLeft < $sleepTime) {
        return [];
    }

    $mh = curl_multi_init();

    $curls = [];
    foreach($urls as $id => $url) {
        // create both cURL resources
        $curls[$id] = curl_init();
        curl_setopt_array($curls[$id], 
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_CUSTOMREQUEST => "GET",
            ]
        );
        if ($timeLeft) {
            curl_setopt($curls[$id], CURLOPT_TIMEOUT, min($maxTime, max($timeLeft, $sleepTime)));
        }
        curl_multi_add_handle($mh, $curls[$id]);
    }

    //execute the multi handle
    $started = null;
    do {
        $status = curl_multi_exec($mh, $active);
        if (!$started) {
            sleep($sleepTime);
            $started = true;
        }
        if ($active) {
            // Wait a short time for more activity
            curl_multi_select($mh);
        }
    } while ($active && $status == CURLM_OK);

    $htmls = [];
    foreach($curls as $id => $ch) {
        $res = curl_multi_getcontent($ch);  
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode == 200) {
            $htmls[$id] = $res;
        }
        if (curl_errno($ch)) {
            errorLog(['curlMultiGet', $id, curl_error($ch)]);
        }        
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);

    return $htmls; // [id => html]
}

?>