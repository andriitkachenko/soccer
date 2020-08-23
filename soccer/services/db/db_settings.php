<?php
declare(strict_types=1);

require_once __DIR__ . '/db_config.php';

interface iDbSettings {
    public function getServer() : string;
    public function getName() : string;
    public function getUser() : string;
    public function getPassword() : string;
}

class DbSettings implements iDbSettings {
    private $local = true;

    public function __construct (bool $local) {
        $this->local = $local;
    }

    public function getServer() : string {
        return $this->local ? DB_SERVER_DEV : DB_SERVER;
    }

    public function getName() : string {
        return $this->local ? DB_NAME_DEV : DB_NAME;
    }

    public function getUser() : string {
        return $this->local ? DB_USER_DEV : DB_USER;
    }

    public function getPassword() : string {
        return $this->local ? DB_PASSWORD_DEV : DB_PASSWORD;
    }
}

?>