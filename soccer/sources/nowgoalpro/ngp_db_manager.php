<?php

require_once __DIR__ . '/../../php/utils.php';
require_once __DIR__ . '/db/new_games_table.php';
require_once __DIR__ . '/db/leagues_table.php';
require_once __DIR__ . '/db/teams_table.php';
require_once __DIR__ . '/db/live_games_table.php';
require_once __DIR__ . '/db/games_table.php';
require_once __DIR__ . '/db/stats_table.php';


    interface iDbManager {
        public function insertLog(Log $log);
    }

    class NgpDbManager implements iDbManager {
        private $dbConn = null;
        private $newGamesTable = null;
        private $leaguesTable = null;
        private $liveGamesTable = null;
        private $teamsTable = null;
        private $gamesTable = null;
        private $statsTable = null;

        function __construct(DbConnection $dbConnection) {
            $this->dbConn = $dbConnection;
            $this->newGamesTable = new NgpNewGamesTable($dbConnection);
            $this->leaguesTable = new NgpLeaguesTable($dbConnection);
            $this->teamsTable = new NgpTeamsTable($dbConnection);
            $this->liveGamesTable = new NgpLiveGamesTable($dbConnection);
            $this->gamesTable = new NgpGamesTable($dbConnection);
            $this->statsTable = new NgpStatsTable($dbConnection);
        }

        public function getLastError() { return $this->dbConn->getLastError(); }
        
        public function loadNewGames($max) { return $this->newGamesTable->load($max); }
        public function truncateNewGames() { return $this->newGamesTable->truncate(); }
        public function deleteNewGames($ids) { return $this->newGamesTable->delete($ids); }
        public function insertNewGames($games) { return $this->newGamesTable->insert($games); }
        
        public function insertLeagues($leagues) { return $this->leaguesTable->insert($leagues); }    
        
        public function insertTeams($teams) { return $this->teamsTable->insert($teams); }    

        public function insertGames($games) { return $this->gamesTable->insert($games); }
        public function updateGames($games) { return $this->gamesTable->update($games); }

        public function truncateLiveGames() { return $this->liveGamesTable->truncate(); }
        public function loadLiveTrackableGames() { return $this->liveGamesTable->loadTrackable(); }
        public function loadFinishedAndNonTrackableGames() { 
            return $this->liveGamesTable->loadFinishedAndNonTrackable(); 
        }
        public function updateLiveGames($stats) { 
            return 
                $this->liveGamesTable->update($stats)
                && $this->statsTable->insert($stats); 
        }    
        public function deleteLiveGames($games) { 
            return $this->liveGamesTable->delete(array_keys($games));
        }
        public function insertLiveGames($games) { 
            $ok = $this->gamesTable->insert($games)
                && $this->liveGamesTable->insert($games)
                && $this->statsTable->insert($games);
            return $ok;
        }    

        public function insertLog(Log $log) {
            $error = +$log->hasError();
            $logText = $log->get();
            $trackable =  $log->getTrackableGames() === null ? 'NULL' : $log->getTrackableGames();
            $live =  $log->getLiveGames() === null ? 'NULL' : $log->getLiveGames();
            $withStatistics = $log->getGamesWithStatistics() === null ? 'NULL' : $log->getGamesWithStatistics();
            $query = 
<<<SQL
INSERT INTO `cron_log` (`error`, `log`, `trackable_games`, `live_games`, `games_with_statistics`) 
VALUES ($error, '$logText', $trackable, $live, $withStatistics);
SQL;
            $ok = exec_query($this->dbConn, $query);  
            return $ok ? true : $query; 
        }

    public function getExistingGameIds($ids) {
        if (!is_array($ids)) {
            errorLog("getExistingGameIds", "IDs is not array");
            return false;
        }
        if (empty($ids)) return [];

        $ids = implode(',', $ids);
        $query = 
<<<SQL
SELECT `game_id` FROM `ngp_new_games` WHERE `game_id` IN ($ids)
UNION
SELECT `game_id` FROM `ngp_live_games` WHERE `game_id` IN ($ids)
UNION
SELECT `game_id` FROM `ngp_games` WHERE `game_id` IN ($ids);
SQL;
        $res = $this->dbConn->query($query);  
        if ($res === false) return false;

        return $res->fetchAll(PDO::FETCH_COLUMN, 0);
    }
}    
?>
