<?php

require_once __DIR__ . '/../../../php/utils.php';
require_once __DIR__ . '/ngp_table.php';

interface iNgpNewGamesTable {
    public function load($max = null);
    public function insert($games);
    public function delete($ids);
    public function truncate();
}

class NgpNewGamesTable extends NgpTable implements iNgpNewGamesTable {

    public function load($max = null) {
        $limit = $max ? " LIMIT $max" : "";
        $query = 
<<<SQL
SELECT `game_id` as `id`, `start_time`, `min`, `url`, `league_short`, `league_url`, `host`, `host_rank`, `guest`, `guest_rank` 
FROM `ngp_new_games`
$limit;
SQL;
        $res = $this->dbConn->query($query);  
        if ($res === false) {
            return false;
        }
        $res = $res->fetchAll(PDO::FETCH_ASSOC);
        $games = [];
        foreach($res as $g) {
            $games[$g['id']] = (object)$g;
        }
        return $games;
    }

    public function delete($ids) {
        if (!is_array($ids)) return false;
        if (empty($ids)) return true;

        $ids = implode(',', $ids);
        $query = 
<<<SQL
DELETE FROM `ngp_new_games` WHERE `game_id` in ($ids);
SQL;
        return $this->dbConn->exec($query); 
    }

    public function truncate() {
        $query = 
<<<SQL
TRUNCATE TABLE `ngp_new_games`;
SQL;
        return $this->dbConn->exec($query); 
    }

    public function insert($games) {
        if (!is_array($games)) return false;
        if (empty($games)) return true;

        $values = [];
        foreach($games as $g) {
            $values[] = [
                dbInt([$g, 'id']), 
                dbString([$g, 'start_time'], true), 
                dbInt([$g, 'min'], true), 
                dbString([$g, 'url']), 
                dbString([$g, 'league_short'], true), 
                dbString([$g, 'league_url'], true), 
                dbString([$g, 'host']), 
                dbString([$g, 'host_rank'], true), 
                dbString([$g, 'guest']), 
                dbString([$g, 'guest_rank'], true)
            ];
        }
        $values = makeInsertValues($values); 
        $query = 
"INSERT IGNORE INTO `ngp_new_games` 
    (
        `game_id`, `start_time`, `min`, `url`, `league_short`, `league_url`, `host`, `host_rank`, 
        `guest`, `guest_rank`
    ) 
VALUES $values;
";
        return $this->dbConn->exec($query);
    }
}    
?>
