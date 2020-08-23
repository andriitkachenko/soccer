<?php
declare(strict_types=1);

require_once __DIR__ . '/../../php/logs.php';

const PARSEHUB_RUN_ATTEMPTS_MAX  = 5;
const PARSEHUB_RUN_PROJECT_URL_TEMPLATE = 'https://www.parsehub.com/api/v2/projects/%project_token%/run';
const PARSEHUB_RUN_DATA_URL= 'https://www.parsehub.com/api/v2/runs/';

interface iParseHub {
    public function runProject();
    public function getData($runToken);
}

class ParseHub implements iParseHub {
    private $projectToken = "";
    private $apiKey = "";

    public function __construct(string $projectToken, string $apiKey) {
        $this->projectToken = $projectToken;
        $this->apiKey = $apiKey;
    }

    public function getData($runToken) {
        $params = http_build_query(
            [
                "api_key" => $this->apiKey,
                "format" => "json"
            ]);
        $options = [
            'http' => [ 'method' => 'GET' ]
        ];
        $url = PARSEHUB_RUN_DATA_URL . $runToken . '/data?'. $params;
        $result = file_get_contents($url, false, stream_context_create($options));

        if (empty($result)) {
            return false;
        }
        $data = gzdecode($result);
        if ($data === false) {
            return false;
        }
        updateLastParsehubResponseFile($data);
        return json_decode($this->normalizeData($data));
    }

    public function runProject() {
        $run = "";
        $i = 0;
        for (; $i < PARSEHUB_RUN_ATTEMPTS_MAX && !$this->isRunTokenOk($run); $i++) {
            if ($i > 0) {
                sleep(5);
            }
            $run = $this->getRunToken();
        }
        $log_result = updateParsehubLog("Run Project", $run);
        return [ 
            'ok' => $this->isRunTokenOk($run), 
            'run' => $run, 
            'logged' => $log_result, 
            'attempts' => $i++ 
        ];
    }

       private function getRunToken() {
        $params = array(
            "api_key" => $this->apiKey
        );
        $options = [
          'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'content' => http_build_query($params)
          ]
        ];
        $context = stream_context_create($options);
        $url = str_replace('%project_token%', $this->projectToken, PARSEHUB_RUN_PROJECT_URL_TEMPLATE);
        return file_get_contents($url, false, $context);
    }

    private function isRunTokenOk($token_string) {
        /*
        {"run_token": "twhP5qKXX0Dt", "status": "initialized", "md5sum": null, "options_json": "{\"recoveryRules\": \"{}\", \"rotateIPs\": false, \"sendEmail\": false, \"allowPerfectSimulation\": false, \"ignoreDisabledElements\": true, \"webhook\": \"http://livesoccer.96.lt/php/parsehub_webhook.php\", \"outputType\": \"csv\", \"customProxies\": \"\", \"preserveOrder\": false, \"startTemplate\": \"unogoal_template\", \"allowReselection\": false, \"proxyDisableAdblock\": false, \"proxyCustomRotationHybrid\": false, \"maxWorkers\": \"0\", \"loadJs\": true, \"startUrl\": \"https://www.unogoal.life/\", \"startValue\": \"{}\", \"maxPages\": \"0\", \"proxyAllowInsecure\": false}", "custom_proxies": "", "data_ready": 0, "template_pages": {}, "start_time": "2019-10-20T12:51:06.811471", "owner_email": "aatkachenko23@gmail.com", "webhook": "http://livesoccer.96.lt/php/parsehub_webhook.php", "is_empty": false, "project_token": "txg_T0WpxYTc", "end_time": null, "start_running_time": null, "start_url": "https://www.unogoal.life/", "start_value": "{}", "start_template": "unogoal_template", "pages": 0}
            */    
        if (empty($token_string)) {
            return false;
        }
        $token = json_decode($token_string, true);
        return !empty($token)
        && isset($token['run_token']) 
            && isset($token['status']) 
            && $token['status'] == 'initialized';
    }
 
    private function logRunProjectResult($res) {
    }

    private function normalizeData($data) {
        return str_replace("'", '', $data);
    }

    private function deleteParseHubRun($runToken) {
        $params = http_build_query([
            "api_key" => $this->apiKey
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
}

/*


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

*/
?>