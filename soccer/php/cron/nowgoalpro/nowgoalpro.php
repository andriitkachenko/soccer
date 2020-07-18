<?php

const PH_PROJECT_TOKEN = "tW4eVr0vCTQG";
const PH_API_KEY = "tbXb7zgCH0L8";

require_once __DIR__ . '/../../parsehub/parsehub.php';

interface iNowGoalPro {
    public function isParseHubClient() : bool;
    public function runCron1();
    public function runParseHubProject();
    public function parseHubWebHook();
}

class NowGoalPro implements iNowGoalPro {
    
    public function isParseHubClient() {
        return true;
    }

    public function runCron1() {
        // read live games from DB
        // load stats for each live game
        // stop games which are not trackable - at min 20 there is no meaningful stat
        // save events
        // save overall game json
    }
    
    public function runParseHubProject() {
        $ph = new ParseHub(PH_PROJECT_TOKEN, PH_API_KEY);
        $res = $ph.runProject();
        $ph->logRunToken($res);
        return $res;
    }

    public function parseHubWebHook() {
        // check parse hub request
        // if request done - run read request
        // update live games
        // finish finished and stopped games
    }
}

?>