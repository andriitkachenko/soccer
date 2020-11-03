<?php
declare(strict_types = 1);

class NgpGame {
    public $id = null;
    public $league = null;
    public $host = null;
    public $host_rank = null;
    public $guest = null;
    public $guest_rank = null;
    public $start_time = null;
    public $state = null;
    public $trackable = null;

    public function __construct(int $id, int $start_time, NgpLeague $league, NgpTeam $host, string $host_rank, 
                                NgpTeam $guest, string $guest_rank) {
        $this->id = $id;
        $this->start_time =$start_time;
        $this->league = $league;
        $this->host = $host;
        $this->host_rank = $host_rank;
        $this->guest = $guest;
        $this->guest_rank = $guest_rank; 
    }
}

?>