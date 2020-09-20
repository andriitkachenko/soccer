<?php

//require_once __DIR__ . '/../../db/db_utils.php';
//require_once __DIR__ . '/../../logs.php';
//require_once __DIR__ . '/../../log/log.php';
require_once __DIR__ . '/../../php/utils.php';


    interface iDbManager {
        public function insertLog(Log $log);
        public function insertStats($stats);
        public function updateGames($games);
    }

    interface  iNgpDbManager {
        public function getExistingGameIds();
        public function saveLiveGames($liveGames) : bool;
        public function deleteNewGames($ids);
        public function insertNewGames($games);
    }

    class NgpDbManager implements iDbManager,iNgpDbManager {
        private $dbConn = null;

        function __construct(DbConnection $dbConnection) {
            $this->dbConn = $dbConnection->get();
        }

        function __destruct() {
            $this->dbConn = null;
        }

        public function deleteNewGames($ids) {
            if (!is_array($ids)) {
                return false;
            }
            if (empty($ids)) {
                return true;
            }
            $ids = implode(',', $ids);
            $query = "DELETE FROM `ngp_new_games` WHERE `game_id` in ($ids);";
            $ok = $this->dbConn->exec($query);
            return $ok ? true : $this->dbConn->getLastError; 
        }

        public function insertNewGames($games) {
            if (!is_array($games)) {
                return false;
            }
            if (empty($games)) {
                return true;
            }
            $values = [];
            foreach($games as $g) {
                $v = [
                    dbInt($g, 'id'), 
                    dbString($g, 'start_time', true), 
                    dbInt($g, 'min', true), 
                    dbString($g, 'url'), 
                    dbString($g, 'league_short', true), 
                    dbString($g, 'league_url', true), 
                    dbString($g, 'host'), 
                    dbString($g, 'host_rank', true), 
                    dbString($g, 'guest'), 
                    dbString($g, 'guest_rank', true)
                ];
                $v = implode(',', $v);
                $values[] =  "($v)";
            }
            $values = implode(',', $values); 
            $query = 
                "INSERT IGNORE 
                INTO `ngp_new_games` (`game_id`, `start_time`, `min`, `url`, `league_short`, `league_url`, `host`, `host_rank`, `guest`, `guest_rank`) 
                VALUES $values;";
            $ok = $this->dbConn->exec($query);
            return $ok ? true : $this->dbConn->getLastError;  
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
            $ok = $this->dbConn->exec($query);  
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
 
    private function insertTeams($teams) {
        /*
        create table if not exists `ngp_teams` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `team_id` INT NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `url` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY (`team_id`)
        ) ENGINE=InnoDB, CHARACTER SET=UTF8;
         */
        if (empty($teams)) {
            return true;
        }

        $values = [];
        foreach($teams as $t) {
            if (empty($t['id']) || empty($t['title']) || empty($t['url'])) {
                continue;
            }
            $values[] = "(" . implode(',', [$t['id'], $t['title'], $t['url']]) . ")";
        }
        $values = implode($values, ', ');   
        $query = 
        "INSERT IGNORE INTO `ngp_teams` (
                `team_id`, 
                `title`,
                `url`
            ) 
            VALUES $values;
        ";
        $ok = $this->dbConn->exec($query);  
        return $ok ? true : $query;   
    }

    private function insertLeagues($leagues) {
        /*
        create table if not exists `ngp_leagues` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `league_id` INT NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `url` VARCHAR(255) NOT NULL,
            `short` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY (`league_id`)
        ) ENGINE=InnoDB, CHARACTER SET=UTF8;
         */
        if (empty($leagues)) {
            return true;
        }
        $values = [];
        foreach($leagues as $l) {
            if (empty($l['id']) || empty($l['short']) || empty($l['url'])) {
                return true;
            }
            $id = $l['id'];
            $title = empty($l['title']) ? 'NULL' : "'" . $l['title'] . "'";
            $short = $l['short'];
            $url = $l['url'];
            $values[] = "($id, $title, '$short', '$url')";
        }
        $values = implode($values, ', ');   
        $query = 
        "INSERT IGNORE INTO `ngp_teams` (
                `league_id`, 
                `title`,
                `short`,
                `url`
            ) 
            VALUES $values;
        ";
        $ok = $this->dbConn->exec($query);  
        return $ok ? true : $query;   
    }    

    public function  getExistingGameIds() {
        $query = "SELECT `game_id` FROM `ngp_new_games`";
        $res = $this->dbConn->query($query);  
        if ($res === false) {
            return [false, $this->dbConn->getLastError];
        }
        $res = $res->fetchAll(PDO::FETCH_COLUMN, 0);
        return [true, $res];
    }

    public function saveLiveGames($liveGames) : bool {
        if (empty($liveGames)) {
            return true;
        }
        // leagues
        $leagues = [];
        foreach($liveGames as $g) {
            $league = $g['stat']['teams']['league'];
            $league['short'] = $g['game']['league_short'];
            $leagues[] = $league;
        }
        $ok = insertLeagues($leagues);
        if (!$ok) {
            return false;
        }
        // teams
        $teams = [];
        foreach($liveGames as $g) {
            $teams[] = $g['stat']['teams']['host'];
            $teams[] = $g['stat']['teams']['guest'];
        }
        $ok = insertTeams($teams);
        if (!$ok) {
            return false;
        }
        //
        $values = [];
        foreach($liveGames as $g) {
            $gameValues = [
                $g['game']['id'], //`game_id` INT UNSIGNED NOT NULL,
                $g['game']['url'],//`url` VARCHAR(255) NOT NULL,
                $g['stat']['teams']['league']['id'],//`league_id` int DEFAULT NULL,
                date('Y-m-d H:i:s', $g['stat']['status']['start']),//`start_time` DATETIME NOT NULL,
                date('Y-m-d H:i:s', $g['stat']['status']['start_real']), //`start_real` DATETIME NOT NULL,
                $g['stat']['teams']['host']['id'], //`host_id` int NOT NULL,
                $g['game']['host_rank'], //`host_rank` VARCHAR(10),
                $g['stat']['teams']['guest']['id'], //`guest_id` int NOT NULL,
                $g['game']['game_rank'],  //`guest_rank` VARCHAR(10),
                'null', //`trackable` TINYINT(1) DEFAULT NULL,
                'null',//`description` TEXT DEFAULT NULL,
                date('Y-m-d H:i:s')//`timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,                
            ];
            $values[] = "(" . implode(',', $gameValues) . ")";
        }
        $values = implode(',', $values);
        $query = "INSERT IGNORE INTO `ngp_live_games`() VALUES $values
        ";
        $res = $this->dbConn->exec($query);  
        return $res != false || $res === 0;
    }

}    
?>
