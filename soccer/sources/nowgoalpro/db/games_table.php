<?php

interface iNgpGamesTable {
    public function insert($games);
    public function update($games);
}

class NgpGamesTable extends NgpTable implements iNgpGamesTable {

    public function insert($games) {
       
        if (!is_array($games)) return false;
        if (empty($games)) return true;

        $values = [];
        foreach($games as $g) {
            $values[] = [
                dbInt([$g, 'id']), 
                dbDatetime([$g, 'status', 'start_time']), 
                dbInt([$g, 'league', 'id'], true), 
                dbInt([$g, 'host', 'id']), 
                dbString([$g, 'host', 'rank'], true), 
                dbInt([$g, 'guest', 'id']), 
                dbString([$g, 'guest', 'rank'], true)
            ];
        }
        $values = makeInsertValues($values); 
        $query = 
<<<SQL
INSERT INTO `ngp_games` (
    `game_id`, `start_time`, `league_id`, `host_id`, `host_rank`, `guest_id`, `guest_rank`
) 
VALUES $values
ON DUPLICATE KEY UPDATE
    `game_id`=VALUES(`game_id`),
    `start_time`=VALUES(`start_time`),
    `league_id`=VALUES(`league_id`), 
    `host_id`=VALUES(`host_id`), 
    `host_rank`=VALUES(`host_rank`), 
    `guest_id`=VALUES(`guest_id`), 
    `guest_rank`=VALUES(`guest_rank`);
SQL;

        return $this->dbConn->exec($query);          
    }

    public function update($games) {
        if (!is_array($games)) return false;
        if (empty($games)) return true;

        $stateCases = [];
        foreach($games as $g) {
            $id = dbInt([$g, 'id']); 
            $state = dbInt([$g, 'status', 'state']); 
            $stateCases[] = "WHEN $id THEN $state";
        }
        $stateCase = implode(' ', $stateCases); 

        $trackCases = [];
        foreach($games as $g) {
            $id = dbInt([$g, 'id']); 
            $track = dbInt([$g, 'status', 'trackable']); 
            $trackCases[] = "WHEN $id THEN $track";
        }
        $trackCase = implode(' ', $trackCases); 

        $finishStat = [];
        foreach($games as $g) {
            $id = dbInt([$g, 'id']); 
            $stat = dbString(makeStatJson($g), true); 
            $trackCases[] = "WHEN $id THEN $stat";
        }
        $trackCase = implode(' ', $trackCases); 

        $ids = implode(',', array_keys($games));
        $query = 
<<<SQL
UPDATE `ngp_games` 
SET 
    `state` = CASE `game_id` $stateCase END,
    `trackable` = CASE `game_id` $trackCase END
WHERE 
    `game_id` IN ($ids);
SQL;
        
        return $this->dbConn->exec($query);          
    }

}

?>