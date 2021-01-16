<?php

require_once __DIR__ . '/../../../php/utils.php';
require_once __DIR__ . '/ngp_table.php';

interface iNgpAccessTable {
    public function insert($ip, $agent);
}

class NgpAccessTable extends NgpTable implements iNgpAccessTable  {

    public function insert($ip, $agent) {
        $query = 
<<<SQL
INSERT INTO `ngp_access` (`ip`, `agent`) 
    VALUES ('$ip', '$agent')
    ON DUPLICATE KEY UPDATE
        `last_access` = NOW();
SQL;
        return $this->dbConn->exec($query);   
    }    
}    
?>
