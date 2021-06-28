<?php
declare(strict_types=1);

require_once __DIR__ . '/../../php/logs.php';
require_once __DIR__ . '/../../php/time.php';
require_once __DIR__ . '/parsehub_utils.php';

const PH_RUN_ATTEMPTS_MAX  = 5;
const PH_GET_PROJECT_URL_TEMPLATE = 'https://www.parsehub.com/api/v2/projects/%project_token%?api_key=%api_key%&offset=0';
const PH_RUN_PROJECT_URL_TEMPLATE = 'https://www.parsehub.com/api/v2/projects/%project_token%/run';
const PH_RUN_DATA_URL= 'https://www.parsehub.com/api/v2/runs/';

interface iParseHub {
    public function run_project();
    public function get_data($run_token);
    public function delete_run($run_token);
    public function clean_up_project();
}

class ParseHub implements iParseHub {
    private $project_token = "";
    private $api_key = "";
    private $id = "";

    public function __construct(string $id, string $project_token, string $api_key) {
        $this->id = $id;
        $this->project_token = $project_token;
        $this->api_key = $api_key;
    }

    public function get_data($run_token) {
        $params = http_build_query(
            [
                "api_key" => $this->api_key,
                "format" => "json"
            ]);
        $options = [
            'http' => [ 'method' => 'GET' ]
        ];
        $url = PH_RUN_DATA_URL . $run_token . '/data?'. $params;
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
            'data' => json_decode($this->normalize_data($data), false)
        ];
    }

    public function run_project() {
        $run = [];
        $i = 0;
        for (; $i < PH_RUN_ATTEMPTS_MAX && !$this->is_run_data_ok($run); $i++) {
            if ($i > 0) {
                sleep(5);
            }
            $run = json_decode($this->get_run_token(), true);
        }
        $log_result = parsehub_run_log("Run Project", json_encode(reduce_run_data($run)));
        return [ 
            'token' => $run['run_token'], 
            'attempts' => $i++,
            'ok' => $this->is_run_data_ok($run), 
            'logged' => $log_result
        ];
    }

       private function get_run_token() {
        $params = array(
            "api_key" => $this->api_key
        );
        $options = [
          'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'content' => http_build_query($params)
          ]
        ];
        $context = stream_context_create($options);
        $url = str_replace('%project_token%', $this->project_token, PH_RUN_PROJECT_URL_TEMPLATE);
        return file_get_contents($url, false, $context);
    }

    private function is_run_data_ok($run) {
        /*
        {"run_token": "twhP5qKXX0Dt", "status": "initialized", "md5sum": null, "options_json": "{\"recoveryRules\": \"{}\", \"rotateIPs\": false, \"sendEmail\": false, \"allowPerfectSimulation\": false, \"ignoreDisabledElements\": true, \"webhook\": \"http://livesoccer.96.lt/php/parsehub_webhook.php\", \"outputType\": \"csv\", \"customProxies\": \"\", \"preserveOrder\": false, \"startTemplate\": \"unogoal_template\", \"allowReselection\": false, \"proxyDisableAdblock\": false, \"proxyCustomRotationHybrid\": false, \"maxWorkers\": \"0\", \"loadJs\": true, \"startUrl\": \"https://www.unogoal.life/\", \"startValue\": \"{}\", \"maxPages\": \"0\", \"proxyAllowInsecure\": false}", "custom_proxies": "", "data_ready": 0, "template_pages": {}, "start_time": "2019-10-20T12:51:06.811471", "owner_email": "aatkachenko23@gmail.com", "webhook": "http://livesoccer.96.lt/php/parsehub_webhook.php", "is_empty": false, "project_token": "txg_T0WpxYTc", "end_time": null, "start_running_time": null, "start_url": "https://www.unogoal.life/", "start_value": "{}", "start_template": "unogoal_template", "pages": 0}
            */    
        return !empty($run)
            && !empty($run['run_token']) 
            && !empty($run['status']) 
            && $run['status'] == 'initialized';
    }
 
    private function normalize_data($data) {
        return str_replace("'", '', $data);
    }

    public function delete_run($run_token) {
        $params = http_build_query([
            "api_key" => $this->api_key
        ]);
        $options = [
            'http' => [ 'method' => 'DELETE' ]
        ];
        $result = file_get_contents(
            PH_RUN_DATA_URL . $run_token . '?'. $params,
            false,
            stream_context_create($options)
        );
        parsehub_run_log("Delete Run", $result);
        return $result;
    }

    public function clean_up_project() {
        parsehub_run_log("Clean-up project", "started");
        $url = str_replace('%api_key%', $this->api_key, 
                str_replace('%project_token%', $this->project_token, PH_GET_PROJECT_URL_TEMPLATE)
            );
        $options = [
            'http' => [ 'method' => 'GET' ]
        ];
        $result = file_get_contents($url, false, stream_context_create($options));
        $result = json_decode($result, true);
        if (!$result || !isset($result['run_list']) || !is_array($result['run_list'])) {
            return false;
        }
        $deleted = [];
        foreach ($result['run_list'] as $r) {
            if (!empty($r['run_token'])) {
                $deleted[] = $this->delete_run($r['run_token']);
            }
        }
        parsehub_run_log("Clean-up project: ", empty($deleted) ? 'nothing to clean-up' : implode('; ', $deleted));
        return true;
    }
}
?>
