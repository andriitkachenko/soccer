<?php
declare(strict_types=1);

require_once __DIR__ . '/../../php/logs.php';
require_once __DIR__ . '/../../php/time.php';
require_once __DIR__ . '/parsehub_utils.php';

const PARSEHUB_RUN_ATTEMPTS_MAX  = 5;
const PARSEHUB_RUN_PROJECT_URL_TEMPLATE = 'https://www.parsehub.com/api/v2/projects/%project_token%/run';
const PARSEHUB_RUN_DATA_URL= 'https://www.parsehub.com/api/v2/runs/';

interface iParseHub {
    public function runProject();
    public function getData($runToken);
    public function deleteParseHubRun($runToken);
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
        return [
            'raw' => $data, 
            'data' => json_decode($this->normalizeData($data), false)
        ];
    }

    public function runProject() {
        $run = [];
        $i = 0;
        for (; $i < PARSEHUB_RUN_ATTEMPTS_MAX && !$this->isRunTokenOk($run); $i++) {
            if ($i > 0) {
                sleep(5);
            }
            $run = json_decode($this->getRunToken(), true);
        }
        $log_result = parsehub_run_log("Run Project", json_encode(reduceRunData($run)));
        return [ 
            'token' => $run['run_token'], 
            'attempts' => $i++,
            'ok' => $this->isRunTokenOk($run), 
            'logged' => $log_result
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

    private function isRunTokenOk($run) {
        /*
        {"run_token": "twhP5qKXX0Dt", "status": "initialized", "md5sum": null, "options_json": "{\"recoveryRules\": \"{}\", \"rotateIPs\": false, \"sendEmail\": false, \"allowPerfectSimulation\": false, \"ignoreDisabledElements\": true, \"webhook\": \"http://livesoccer.96.lt/php/parsehub_webhook.php\", \"outputType\": \"csv\", \"customProxies\": \"\", \"preserveOrder\": false, \"startTemplate\": \"unogoal_template\", \"allowReselection\": false, \"proxyDisableAdblock\": false, \"proxyCustomRotationHybrid\": false, \"maxWorkers\": \"0\", \"loadJs\": true, \"startUrl\": \"https://www.unogoal.life/\", \"startValue\": \"{}\", \"maxPages\": \"0\", \"proxyAllowInsecure\": false}", "custom_proxies": "", "data_ready": 0, "template_pages": {}, "start_time": "2019-10-20T12:51:06.811471", "owner_email": "aatkachenko23@gmail.com", "webhook": "http://livesoccer.96.lt/php/parsehub_webhook.php", "is_empty": false, "project_token": "txg_T0WpxYTc", "end_time": null, "start_running_time": null, "start_url": "https://www.unogoal.life/", "start_value": "{}", "start_template": "unogoal_template", "pages": 0}
            */    
        return !empty($run)
            && !empty($run['run_token']) 
            && !empty($run['status']) 
            && $run['status'] == 'initialized';
    }
 
    private function normalizeData($data) {
        return str_replace("'", '', $data);
    }

    public function deleteParseHubRun($runToken) {
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
        parsehub_run_log("Delete Run", $result);
        return $result;
    }
}
?>
