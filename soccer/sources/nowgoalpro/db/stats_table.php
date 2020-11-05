<?php

interface iNgpStatsTable {
    public function insert($games);
}

class NgpStatsTable extends NgpTable implements iNgpStatsTable {

    public function insert($games) {
        if (!is_array($games)) return false;
        if (empty($games)) return true;

        $values = [];
        foreach($games as $g) {
            if (empty($g->status->min) || $g->status->trackable === 0) {
                continue;
            }
            $stat = dbJson([$g, 'stat', 'host']);
            $values[] = [
                dbInt([$g, 'status', 'game_id']), 
                dbInt([$g, 'host', 'id']), 
                dbInt([$g, 'status', 'min']), 
                $stat,
                dbHash($stat)
            ];
            $stat = dbJson([$g, 'stat', 'guest']);
            $hash = sha1($stat);
            $values[] = [
                dbInt([$g, 'status', 'game_id']), 
                dbInt([$g, 'guest', 'id']), 
                dbInt([$g, 'status', 'min']), 
                $stat,
                dbHash($stat)
            ];

        }
        if (empty($values)) {
            return true;
        }        
        $values = makeInsertValues($values); 
        $query = 
<<<SQL
    INSERT IGNORE INTO `ngp_stats` (`game_id`, `team_id`, `min`, `stat`, `hash`) 
    VALUES $values;
SQL;

        return $this->dbConn->exec($query);          
    }
}

?>