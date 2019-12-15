<?php

require_once __DIR__ . '/logs.php';
require_once __DIR__ . '/games.php';


function isRunTokenOk($token_string) {
/*
{"run_token": "twhP5qKXX0Dt", "status": "initialized", "md5sum": null, "options_json": "{\"recoveryRules\": \"{}\", \"rotateIPs\": false, \"sendEmail\": false, \"allowPerfectSimulation\": false, \"ignoreDisabledElements\": true, \"webhook\": \"http://livesoccer.96.lt/php/parsehub_webhook.php\", \"outputType\": \"csv\", \"customProxies\": \"\", \"preserveOrder\": false, \"startTemplate\": \"unogoal_template\", \"allowReselection\": false, \"proxyDisableAdblock\": false, \"proxyCustomRotationHybrid\": false, \"maxWorkers\": \"0\", \"loadJs\": true, \"startUrl\": \"https://www.unogoal.life/\", \"startValue\": \"{}\", \"maxPages\": \"0\", \"proxyAllowInsecure\": false}", "custom_proxies": "", "data_ready": 0, "template_pages": {}, "start_time": "2019-10-20T12:51:06.811471", "owner_email": "aatkachenko23@gmail.com", "webhook": "http://livesoccer.96.lt/php/parsehub_webhook.php", "is_empty": false, "project_token": "txg_T0WpxYTc", "end_time": null, "start_running_time": null, "start_url": "https://www.unogoal.life/", "start_value": "{}", "start_template": "unogoal_template", "pages": 0}
*/    
    if (empty($token_string))
        return false;
    $token = json_decode($token_string, true);
    return !empty($token)
        && isset($token['run_token']) 
        && isset($token['status']) 
        && $token['status'] == 'initialized';
}

function getRunToken() {
    $params = array(
        "api_key" => PARSEHUB_API_KEY
    );
    $options = [
      'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
        'content' => http_build_query($params)
      ]
    ];
    $context = stream_context_create($options);
    return file_get_contents(PARSEHUB_RUN_PROJECT_URL, false, $context);
}

function getRunTokenSeries() {
    $run = "";
    $i = 0;
    for (; $i < PARSEHUB_RUN_ATTEMPTS_MAX && !isRunTokenOk($run); $i++) {
        if ($i > 0) {
            sleep(10);
        }
        $run = getRunToken();
    }
    $log_result = updateParsehubLog("Run Project", $run);
    return [ 
        'ok' => isRunTokenOk($run), 
        'run' => $run, 
        'logged' => $log_result, 
        'attempts' => $i++ 
    ];
}

function normalizeParseHubData($data) {
    return str_replace("'", '', $data);
}

function getParseHubData($runToken) {
    $params = http_build_query(
        [
            "api_key" => PARSEHUB_API_KEY,
            "format" => "json"
        ]);
    $options = [
        'http' => [ 'method' => 'GET' ]
    ];
    $result = file_get_contents(
        PARSEHUB_RUN_DATA_URL . $runToken . '/data?'. $params,
        false,
        stream_context_create($options)
    );
    if (empty($result)) {
        return [];
    }
    $data = gzdecode($result);
    if ($data === false) {
        return [];
    }
    updateLastParsehubResponseFile($data);
    $data = json_decode(normalizeParseHubData($data));
    if ($data && isset($data->selection1)) {
        $data = $data->selection1;
    } else {
        $data = [];
    }
    $games = [];
    //{"data":"KWSL09:004 Okzhetpes (w)0 - 0Namys (w)","data-id":"tr1_1783182"}
    //{"game":[{"id":"mt_1784117","start":"06:00","score":"0 - 0","time":"1","host":"Tri Elang United","guest":"Amesiu United","league":"Indo D3","first_half":""}
    
    foreach ($data as $game) {
        if (!isset($game->game)) {
            continue;
        }
        $game = $game->game;
        if (!is_array($game) || !count($game)) {
            continue;
        }
        $game = $game[0];
        $games[] = [
            'id' => trim(str_replace("mt_", "", $game->id)),
            'league' =>  trim($game->league),
            'start_time' =>  trim($game->start),
            'game_time' => trim($game->time),
            'host' => trim($game->host),
            'guest' => trim($game->guest),
            'score' => trim($game->score)
        ];
    }
    $games = filterAndPrepairGames($games);
    updateParsehubLog("Get Run Data", count($games) . ' games found');
    deleteParseHubRun($runToken);
    return $games;
}

function deleteParseHubRun($runToken) {
    $params = http_build_query([
        "api_key" => PARSEHUB_API_KEY
    ]);
    $options = [
        'http' => [ 'method' => 'DELETE' ]
    ];
    $result = file_get_contents(
        PARSEHUB_RUN_DATA_URL . $runToken . '?'. $params,
        false,
        stream_context_create($options)
    );
    updateParsehubLog("Delete Run", $result);
    return $result;
}

?>