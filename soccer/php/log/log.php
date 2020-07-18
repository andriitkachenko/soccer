<?php 
declare(strict_types = 1);

require_once __DIR__ . '/log_record.php';

    interface iLog {
        public function setTrackableGames(int $count) : void;
        public function getTrackableGames();
        public function setLiveGames(int $count) : void;
        public function getLiveGames();
        public function setGamesWithStatistics(int $count) : void;
        public function getGamesWithStatistics();
        public function append(string $log, int $logType = LOG_TYPE_INFO) : void;
        public function get(): string;
        public function hasError(): bool;
    }

    class Log implements iLog {
        private $records;
        private $trackableGames;
        private $liveGames;
        private $gamesWithStatistics;

        function __construct () {
            $this->records = [];
            $this->trackableGames = null;
            $this->liveGames = null;
            $this->gamesWithStatistics = null;
        }

        public function append(string $log, int $logType = LOG_TYPE_INFO) : void {
            $record = new LogRecord($logType, $log);
            $this->records[] = $record;
        }
        
        public function get() : string {
            return implode(' === ', array_map(function($r) {return $r->get(); }, $this->records));
        }

        public function hasError() : bool {
            $error = false;
            foreach ($this->records as $log) {
                if ($log->isError()) {
                    $error = true;
                    break;
                }
            }
            return $error;
        }

        public function setTrackableGames(int $count) : void {
            $this->trackableGames = $count;
            $this->append("$count trackable games");
        }
        
        public function setLiveGames(int $count)  : void {
            $this->liveGames = $count;
            $this->append("$count live games");
        }
        
        public function setGamesWithStatistics(int $count)  : void {
            $this->gamesWithStatistics = $count;
            $this->append("$count games with statistics");
        }

        public function getTrackableGames() {
            return $this->trackableGames;
        }

        public function getLiveGames() {
            return $this->liveGames;
        }
        
        public function getGamesWithStatistics() {
            return $this->gamesWithStatistics;
        }

    }
?>