<?php

require_once __DIR__ . '/../../../php/utils.php';
require_once __DIR__ . '/ngp_table.php';

interface iNgpTeamsTable {
    public function insert($teams);
}

class NgpTeamsTable extends NgpTable implements iNgpTeamsTable {

    public function insert($teams) {
         if (!is_array($teams)) return false;
        if (empty($teams)) return true;

        $values = [];
        foreach($teams as $t) {
            $values[] = [
                dbInt([$t, 'id']), 
                dbString([$t, 'title'], false), 
                dbString([$t, 'url']), 
            ];
        }
        $values = makeInsertValues($values); 

        $query = 
            "INSERT INTO `ngp_teams` (
                    `team_id`, 
                    `title`,
                    `url`
                ) 
                VALUES $values
                ON DUPLICATE KEY UPDATE
                    `team_id`=VALUES(`team_id`),
                    `title`=VALUES(`title`),
                    `url`=VALUES(`url`)            
            ;";
        return $this->dbConn->exec($query);  
    }    
}    
?>
