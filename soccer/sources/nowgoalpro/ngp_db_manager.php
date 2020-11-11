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

    public function loadLiveLastStats() {
        $query = 
<<<SQL
select 
	gs.`game_id` as `id`, 
	gs.`start_time` as `start_time`, 
	l.`start_real` as `start_real`, 
	cast(l.`state` as unsigned) as `state`, 
    lg.`title_short` as `league`,
    h.`title` as `host`, 
    gs.`host_rank` as `host_rank`,
    g.`title` as `guest`, 
    gs.`guest_rank` as `guest_rank`, 
    cast(JSON_EXTRACT(l.`last_stat` , '$.min') as unsigned) as `min`,
    JSON_EXTRACT(l.`last_stat` , '$.host') as `host_stat`,
    JSON_EXTRACT(l.`last_stat` , '$.guest') as `guest_stat`
from `ngp_games` as gs
inner join `ngp_leagues` as lg on lg.league_id = gs.league_id
inner join `ngp_live_games` as l on l.game_id = gs.game_id
inner join `ngp_teams` as h on gs.host_id = h.team_id
inner join `ngp_teams` as g on gs.guest_id = g.team_id
having `min` > 9
order by `min` desc;
SQL;    
        $res = $this->dbConn->query($query); 
        if ($res === false) return false;

        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [];
        foreach($res as $s) {
            $s['id'] = intval($s['id']);
            $s['state'] = intval($s['state']);
            $s['min'] = intval($s['min']);
            $s['host_stat'] = $this->unifyStat($s['host_stat']);
            $s['guest_stat'] = $this->unifyStat($s['guest_stat']);
            $stats[$s["id"]] = $s;
        }
        return $stats;        
    }

    private function unifyStat($stat) {
        $events = ['sh', 'sg', 'at', 'da', 'bp', 'gl', 'rc', 'yc'];
        $s = json_decode($stat, true);
        if (!is_array($s)) {
            return [];
        }
        return array_filter($s, function($v, $k) use($events) {return in_array($k, $events);}, ARRAY_FILTER_USE_BOTH);
    }

}    
?>
