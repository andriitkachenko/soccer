<?php

require_once __DIR__ . '/../../../php/utils.php';
require_once __DIR__ . '/ngp_table.php';

interface iNgpLeaguesTable {
    public function insert($leagues);
}

class NgpLeaguesTable extends NgpTable implements iNgpLeaguesTable  {

    public function insert($leagues) {
        if (!is_array($leagues)) return false;
        if (empty($leagues)) return true;

        $values = [];
        foreach($leagues as $l) {
            $values[] = [
                dbInt([$l, 'id']), 
                dbString([$l, 'title'], false), 
                dbString([$l, 'title_short'], false), 
                dbString([$l, 'url']), 
            ];
        }
        $values = makeInsertValues($values); 

        $query = 
<<<SQL
INSERT INTO `ngp_leagues` (
        `league_id`, `title`, `title_short`, `url`
    ) 
    VALUES $values
    ON DUPLICATE KEY UPDATE
        `league_id`=VALUES(`league_id`),
        `title`=VALUES(`title`),
        `title_short`=VALUES(`title_short`),
        `url`=VALUES(`url`);
SQL;

        return $this->dbConn->exec($query);   
    }    
}    
?>
