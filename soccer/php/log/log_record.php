<?php 
declare(strict_types = 1);

const LOG_TYPE_INFO = 0;
const LOG_TYPE_ERROR = 1;


    interface iLogRecord {
        public function isError() : bool;
        public function get() : string;
    }

    class LogRecord implements iLogRecord {
        private $logType;
        private $log;

        function __construct (int $logType, string $log) {
            $this->logType = $logType;
            $this->log = $log;
        }    

        public function isError() : bool {
            return $this->logType === LOG_TYPE_ERROR;
        }
        public function get() : string {
            return ($this->isError() ? '"ERROR : ' : '') . $this->log;
        }
    }

?>