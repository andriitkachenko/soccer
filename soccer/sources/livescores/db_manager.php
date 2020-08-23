<?php

require_once __DIR__ . '/../../db/db_utils.php';
require_once __DIR__ . '/../../logs.php';
require_once __DIR__ . '/../../log/log.php';
require_once __DIR__ . '/../../utils.php';


    interface iDbManager {
        public function insertLog(Log $log);
        public function insertStats($stats);
        public function updateGames($games);
    }

    class DbManager implements iDbManager {
        private $dbConn = null;

        function __construct(PDO $dbConnection) {
            $this->dbConn = $dbConnection;
        }

        function __destruct() {
            $this->dbConn = null;
        }
        
        public function insertStats($stats) {
            if (empty($stats)) {
                return true;
            }
            $values = [];
            foreach($stats as $s) {
                $values = array_merge($values, $this->statParams2value($s));
            }
            $values = implode($values, ', ');   
            $query = 
            "INSERT IGNORE INTO `ls_events` (
                    `game_id`, 
                    `min`,
                    `extra`, 
                    `host`, 
                    `event`, 
                    `amount` 
                ) 
                VALUES $values;
            ";
            $ok = exec_query($this->dbConn, $query);  
            return $ok ? true : $query;                      
        }

        public function insertLog(Log $log) {
            $error = +$log->hasError();
            $logText = $log->get();
            $trackable =  $log->getTrackableGames() === null ? 'NULL' : $log->getTrackableGames();
            $live =  $log->getLiveGames() === null ? 'NULL' : $log->getLiveGames();
            $withStatistics = $log->getGamesWithStatistics() === null ? 'NULL' : $log->getGamesWithStatistics();
            $query = 
                "INSERT INTO `cron_log` (`error`, `log`, `trackable_games`, `live_games`, `games_with_statistics`) 
                    VALUES ($error, '$logText', $trackable, $live, $withStatistics);
                ";
            $ok = exec_query($this->dbConn, $query);  
            return $ok ? true : $query; 
        }

        public function updateGames($games) {
            if (empty($games)) {
                return true;
            }
            $values = [];
            foreach($games as $g) {
                if (!empty($g['url'])) {
                    $values[] = $this->gameParams2value($g);
                }
            }
            $values = implode($values, ', ');
            $query = 
                "INSERT INTO `ls_games` (
                        `game_id`, 
                        `url`,
                        `league`, 
                        `start_time`, 
                        `start_timestamp`, 
                        `host`, 
                        `guest`, 
                        `finished`, 
                        `description`
                    ) 
                    VALUES $values 
                    ON DUPLICATE KEY UPDATE 
                        `game_id` = VALUES(`game_id`), 
                        `league` = VALUES(`league`),
                        `start_time` = VALUES(`start_time`),
                        `start_timestamp` = VALUES(`start_timestamp`),
                        `host` = VALUES(`host`),
                        `guest` = VALUES(`guest`),
                        `finished` = VALUES(`finished`),
                        `description` = VALUES(`description`);
                ";
            $ok = exec_query($this->dbConn, $query);  
            return $ok ? true : $query; 
        }
        
        private function gameParams2value($game) {
            $id = $game['id'];
            $url =  $game['url'];
            $league = 'NULL';
            $start_time = date('Y-m-d H:i:00', $game['start']);
            $start_timestamp = $game['start'];
            $host = str_replace("'", "-", $game['host']);
            $guest = str_replace("'", "-", $game['guest']);
            $finished = +(!$game['time']['live'] && !$game['time']['not_started']);
            $descr = $game['time']['time'];
            $descr = $finished && strtolower($game['time']['time']) != 'ft' ? "'$descr'" : 'NULL';
            return "($id, '$url', $league, '$start_time', $start_timestamp, '$host', '$guest', $finished, $descr)";
        }

        private function statParams2value($stat) {
/* Array(
    [host] => Array
        (
            [sg] => 5
            [sh] => 9
            [bp] => 46
            [ck] => 5
            [of] => 1
            [fl] => 18
            [yc] => 4
            [gk] => 6
            [tm] => 1
        )

    [guest] => Array
        (
            [sg] => 4
            [sh] => 4
            [bp] => 54
            [ck] => 7
            [of] => 1
            [fl] => 12
            [yc] => 2
            [gk] => 9
            [tm] => 0
        )

    [game] => Array
        (
            [host] => 1. FC Köln
            [guest] => Mainz 05
            [hostScore] => 2
            [guestScore] => 2
            [time] => Array
                (
                    [time] => FT
                    [live] => 
                    [min] => 0
                    [extra] => 0
                    [not_started] => 0
                )

        )
)
*/              
            $id = $stat['game_id'];
            $min = $stat['game']['time']['min'];
            $extra = empty($stat['game']['time']['extra']) 
                ? 'NULL' 
                : $stat['game']['time']['extra'];
            $values = [];
            $hostGoals =  $stat['game']['hostScore'];
            $guestGoals =  $stat['game']['guestScore'];
            if ($hostGoals) {
                $values[] = "($id, $min, $extra, 1, 'gl', $hostGoals)";
            }
            if ($guestGoals) {
                $values[] = "($id, $min, $extra, 0, 'gl', $guestGoals)";
            }
            foreach($stat['host'] as $event => $amount) {
                if ($amount) {
                    $values[] = "($id, $min, $extra, 1, '$event', $amount)";
                }
            }
            foreach($stat['guest'] as $event => $amount) {
                if ($amount) {
                    $values[] = "($id, $min, $extra, 0, '$event', $amount)";
                }
            }
            return $values;
        }
    }

?>

