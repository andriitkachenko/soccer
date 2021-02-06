<?php

interface iNgpGamesTable {
    public function insert($games, $descrCode = null);
    public function update($games, $descrCode = null);
}

class NgpGamesTable extends NgpTable implements iNgpGamesTable {

    public function insert($games, $descrCode = null) {
       
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
                dbString([$g, 'guest', 'rank'], true),
                dbInt($descrCode, true)
            ];
        }
        $values = makeInsertValues($values); 
        $query = 
<<<SQL
INSERT INTO `ngp_games` (
    `game_id`, `start_time`, `league_id`, `host_id`, `host_rank`, `guest_id`, `guest_rank`, `description`
) 
VALUES $values
ON DUPLICATE KEY UPDATE
    `game_id` = VALUES(`game_id`),
    `start_time` = VALUES(`start_time`),
    `league_id` = VALUES(`league_id`), 
    `host_id` = VALUES(`host_id`), 
    `host_rank` = VALUES(`host_rank`), 
    `guest_id` = VALUES(`guest_id`), 
    `guest_rank` = VALUES(`guest_rank`),
    `description` = VALUES(`description`);
SQL;

        return $this->dbConn->exec($query);          
    }

    public function update($games, $descrCode = null) {
        if (!is_array($games)) return false;
        if (empty($games)) return true;

        $stateCases = [];
        $ids = [];
        foreach($games as $g) {
            $id = dbInt([$g, 'id']); 
            $ids[] = $id;
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

        $ids = implode(',', $ids);

        $description = $descrCode ? ", `description` = $descrCode" : '';
        $query = 
<<<SQL
UPDATE `ngp_games` 
SET 
    `state` = CASE `game_id` $stateCase END,
    `trackable` = CASE `game_id` $trackCase END
    $description
WHERE 
    `game_id` IN ($ids);
SQL;
      
        return $this->dbConn->exec($query);          
    }

}

?>