<?php

require_once __DIR__ . '/../../php/utils.php';
require_once __DIR__ . '/db/new_games_table.php';
require_once __DIR__ . '/db/leagues_table.php';
require_once __DIR__ . '/db/teams_table.php';
require_once __DIR__ . '/db/live_games_table.php';
require_once __DIR__ . '/db/games_table.php';
require_once __DIR__ . '/db/stats_table.php';
require_once __DIR__ . '/db/access_table.php';

const ADDED_LIVE_GAME = 1;
const ARCHIVED_AS_FINISHED_OR_NON_TRACKABLE = 2;
const ARCHIVED_NON_LIVE = 3;

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
        private $accessTable = null;

        function __construct(DbConnection $dbConnection) {
            $this->dbConn = $dbConnection;
            $this->newGamesTable = new NgpNewGamesTable($dbConnection);
            $this->leaguesTable = new NgpLeaguesTable($dbConnection);
            $this->teamsTable = new NgpTeamsTable($dbConnection);
            $this->liveGamesTable = new NgpLiveGamesTable($dbConnection);
            $this->gamesTable = new NgpGamesTable($dbConnection);
            $this->statsTable = new NgpStatsTable($dbConnection);
            $this->accessTable = new NgpAccessTable($dbConnection);
        }

        public function getLastError() { return $this->dbConn->getLastError(); }

        public function insertAccess($ip, $agent) { return $this->accessTable->insert($ip, $agent); }    
        
        public function loadNewGames($max) { return $this->newGamesTable->load($max); }
        public function truncateNewGames() { return $this->newGamesTable->truncate(); }
        public function deleteNewGames($ids) { return $this->newGamesTable->delete($ids); }
        public function insertNewGames($games) { return $this->newGamesTable->insert($games); }
        
        public function insertLeagues($leagues) { return $this->leaguesTable->insert($leagues); }    
        
        public function insertTeams($teams) { return $this->teamsTable->insert($teams); }    

        public function insertGames($games) { return $this->gamesTable->insert($games); }
        public function updateGames($games, $descr) { 
            return 
                $this->statsTable->insert($games)
                && $this->gamesTable->update($games, $descr); 
        }

        public function truncateLiveGames() { return $this->liveGamesTable->truncate(); }
        public function loadExistingLiveGames($ids) { return $this->liveGamesTable->loadByIds($ids); }
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
            $ok = $this->gamesTable->insert($games, ADDED_LIVE_GAME)
                && $this->liveGamesTable->insert($games)
                && $this->statsTable->insert($games);
            return $ok;
        }
        public function untrackLiveGames($games) {
            return $this->liveGamesTable->untrack(array_keys($games)); 
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
having `min` > 10
order by `min` desc;
SQL;    
        $res = $this->dbConn->query($query); 
        if ($res === false) return false;

        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [];
        foreach($res as $s) {
            $s['id'] = intval($s['id']);
            $s['state'] = intval($s['state']);
            $min = intval($s['min']);
            $extra = 0;
            if ($s['state'] == 1 && $min > 45) $extra = $min - 45;
            if ($s['state'] == 3 && $min > 90) $extra = $min - 90;
            $s['min_extra'] = $extra;
            $s['min'] = $min - $extra;
            $s['host_stat'] = $this->unifyStat($s['host_stat']);
            $s['guest_stat'] = $this->unifyStat($s['guest_stat']);
            $stats[$s["id"]] = $s;
        }
        return $stats;        
    }

    public function loadLiveHistoryStats($minAgo) {

        $query = 
<<<SQL
select hs.game_id as `game_id`, hs.`min` as `min`, hs.`stat` as `hstat`, gs.`stat` as `gstat` from
    (
        select ns.`game_id` as `game_id`, ns.`stat` as `stat`, ns.`min` as `min` from `ngp_stats` as ns
        inner join (
            select 
                s.`game_id` as `id`,
                s.`team_id` as `team_id`,
                s.`state` as `state`,
                MAX(s.`timestamp`) as `time`
            from `ngp_games` as gs
            inner join `ngp_stats` as s on gs.`game_id` = s.`game_id` and s.`team_id` = gs.`host_id`
            inner join `ngp_live_games` as l on s.`game_id` = l.`game_id` 
            where l.`trackable` and l.`state` = s.`state` and s.`timestamp` <= date_add(now(), interval -$minAgo minute)
            group by s.`game_id`
            ) as h on ns.`game_id` = h.`id`
        where ns.`team_id` = h.`team_id` and ns.`timestamp` = h.`time`
    ) as hs
inner join 
    (
        select ns.`game_id`, ns.`stat` as `stat` from `ngp_stats` as ns
        inner join (
            select 
                s.`game_id` as `id`,
                s.`team_id` as `team_id`,
                s.`state` as `state`,
                MAX(s.`timestamp`) as `time`
            from `ngp_games` as gs
            inner join `ngp_stats` as s on gs.`game_id` = s.`game_id` and s.`team_id` = gs.`guest_id`
            inner join `ngp_live_games` as l on s.`game_id` = l.`game_id` 
            where l.`trackable` and l.`state` = s.`state` and s.`timestamp` <= date_add(now(), interval -$minAgo minute)
            group by s.`game_id`
            ) as g on ns.`game_id` = g.`id`
        where ns.`team_id` = g.`team_id` and ns.`timestamp` = g.`time`
    ) as gs on hs.`game_id` = gs.`game_id`;
SQL;   
 
        $res = $this->dbConn->query($query); 
        if ($res === false) return false;

        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        
        $history = [];
        foreach($res as $s) {
            $id = intval($s['game_id']);
            $history[$id] = [
                'min' => $s['min'],
                'host_stat' => $this->unifyStat($s['hstat']),
                'guest_stat' => $this->unifyStat($s['gstat'])
            ];
        }
        return $history;        
    }

    public function loadLiveHalfTimeStats() {

        $query = 
<<<SQL
select hs.`game_id` as `game_id`, hs.`state` as `state`, hs.`stat` as `hstat`, gs.`stat` as `gstat` from
    (
        select ns.`game_id` as `game_id`, ns.`state` as `state`, ns.`stat` as `stat` from `ngp_stats` as ns
        inner join (
            select gs.`game_id` as `id`, gs.`host_id` as `team_id`
            from `ngp_games` as gs
            inner join `ngp_live_games` as l on gs.`game_id` = l.`game_id` 
            where l.`trackable` and (l.`state` = 2 or l.`state` = 3)
        ) as h on ns.`game_id` = h.`id` and ns.`team_id` = h.`team_id`
        where ns.state = 2
    ) as hs
inner join 
    (
        select  ns.`game_id` as `game_id`, ns.`stat` as `stat` from `ngp_stats` as ns
        inner join (
            select gs.`game_id` as `id`, gs.`guest_id` as `team_id`
            from `ngp_games` as gs
            inner join `ngp_live_games` as l on gs.`game_id` = l.`game_id` 
            where l.`trackable` and (l.`state` = 2 or l.`state` = 3)
        ) as h on ns.`game_id` = h.`id` and ns.`team_id` = h.`team_id`
        where ns.state = 2
    ) as gs on hs.`game_id` = gs.`game_id`;
SQL;   
 
        $res = $this->dbConn->query($query); 
        if ($res === false) return false;

        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        
        $halftime = [];
        foreach($res as $s) {
            $id = intval($s['game_id']);
            $halftime[$id] = [
                'state' => $s['state'],
                'host_stat' => $this->unifyStat($s['hstat']),
                'guest_stat' => $this->unifyStat($s['gstat'])
            ];
        }
        return $halftime;        
    }


    private function unifyStat($stat) {
        $events = ['sh', 'sg', 'at', 'da', 'bp', 'gl', 'rc', 'yc'];
        $s = json_decode($stat, true);
        if (!is_array($s)) {
            return [];
        }
        $s = array_filter($s, function($v, $k) use($events) {
                return in_array($k, $events);
            }, ARRAY_FILTER_USE_BOTH
        );
        if (isset($s['sh']) && !isset($s['sg'])) {
            $s['sg'] = 0;
        }
        return $s;
    }

}    
?>
