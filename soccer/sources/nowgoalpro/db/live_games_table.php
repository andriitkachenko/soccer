<?php

require_once __DIR__ . '/../../../php/utils.php';
require_once __DIR__ . '/ngp_table.php';
require_once __DIR__ . '/../ngp_config.php';

interface iNgpLiveGamesTable {
    public function insert($games);
    public function update($games);
    public function loadTrackable();
    public function loadFinishedAndNonTrackable();
    public function delete($ids);
    public function truncate();
}

class NgpLiveGamesTable extends NgpTable implements iNgpLiveGamesTable {

    public function insert($games) {
        if (!is_array($games)) return false;
        if (empty($games)) return true;

        $values = [];
        foreach($games as $g) {
            $values[] = [
                dbInt([$g, 'id']), 
                dbString([$g, 'url']), 
                dbDatetime([$g, 'status', 'start_real']), 
                dbInt([$g->status, 'state']), 
                dbInt([$g, 'status', 'trackable'], true),
                dbString(makeStatJson($g), true),
                dbDatetime($this->getNextUpdateTime($g), true)
            ];
        }
        $values = makeInsertValues($values); 
        $query = 
<<<SQL
INSERT INTO `ngp_live_games` (
        `game_id`, `url`, `start_real`, `state`, `trackable`, `last_stat`, `next_update`
    ) 
    VALUES $values
    ON DUPLICATE KEY UPDATE 
        `game_id`=VALUES(`game_id`),
        `url`=VALUES(`url`),
        `start_real`=VALUES(`start_real`), 
        `state`=VALUES(`state`), 
        `trackable`=VALUES(`trackable`), 
        `last_stat`=VALUES(`last_stat`), 
        `next_update`=VALUES(`next_update`);
SQL;

        return $this->dbConn->exec($query);  
    }

    public function update($games) {
        if (!is_array($games)) return false;
        if (empty($games)) return true;

        $values = [];
        foreach($games as $id => $s) {
            if (empty($s)) {
                continue;
            } 
            $values[] = [
                dbInt([$s, 'id']), 
                dbString([$s, 'url']),
                dbDatetime([$s, 'status', 'start_real']), 
                dbInt([$s, 'status', 'state']), 
                dbInt([$s, 'status', 'trackable'], true),
                dbString(makeStatJson($s), true),
                dbDatetime($this->getNextUpdateTime($s), true)
            ];
        }
       
        $values = makeInsertValues($values); 
        $query = 
<<<SQL
INSERT INTO `ngp_live_games` (
        `game_id`, `url`, `start_real`, `state`, `trackable`, `last_stat`, `next_update`
    ) 
    VALUES $values
    ON DUPLICATE KEY UPDATE 
        `game_id`=VALUES(`game_id`),
        `url`=VALUES(`url`),
        `start_real`=VALUES(`start_real`), 
        `state`=VALUES(`state`), 
        `trackable`=VALUES(`trackable`), 
        `last_stat`=VALUES(`last_stat`), 
        `next_update`=VALUES(`next_update`);
SQL;
      
        return $this->dbConn->exec($query);  
    }

    public function loadTrackable() {
        $query = 
<<<SQL
SELECT  `game_id` as `id`, `url`
    FROM `ngp_live_games`
    WHERE (`trackable` iS NULL OR `trackable` = 1) 
        AND (`next_update` IS NULL OR NOW() >= `next_update`)
    ORDER BY (NOW() - `next_update`) DESC;
SQL;
        $res = $this->dbConn->query($query);  
        if ($res === false) return false;

        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        $trackable = [];
        foreach($res as $g) {
            $trackable[$g['id']] = (object)$g;
        }

        return $trackable;
    }
    
    public function loadFinishedAndNonTrackable() {
        $query = 
<<<SQL
SELECT `game_id` as `id`, `state`, `trackable`
    FROM `ngp_live_games` 
    WHERE `trackable`= 0 OR `state` < 1;
SQL;
        $res = $this->dbConn->query($query);  
        if ($res === false) {
            return false;
        }
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $nonTrackable = [];
        foreach($res as $g) {
            $nonTrackable[$g['id']] = (object)$g;
        }
        return $nonTrackable;
    }

    public function delete($ids) {
        if (!is_array($ids)) return false;
        if (empty($ids)) return true;

        $ids = implode(',', $ids);
        $query = "DELETE FROM `ngp_live_games` WHERE `game_id` in ($ids);";
        return $this->dbConn->exec($query);         
    }

    private function getNextUpdateTime($g) {
        if (empty($g->status->state) || empty($g->status->start_real)) {
            return null;
        }
        $start = $g->status->start_real;
        $state = $g->status->state;
        $time = time();
        if ($state == 1 && $time - $start < START_TRACKING) {
            return $start + START_TRACKING;
        }
        if ($state == 2) {
            return $start + BREAK_TIME;
        }
        return $time + STAT_UPDATE_INTERVAL;
    }
    
    public function truncate() {
        $query = "TRUNCATE TABLE `ngp_live_games`";
        return $this->dbConn->exec($query); 
    }
}    
?>
