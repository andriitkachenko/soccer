drop database if exists `ngp`;
create database `ngp`;
use `ngp`;

create table if not exists `ngp_leagues` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`league_id` INT UNSIGNED NOT NULL,
	`title` VARCHAR(255) NOT NULL,
	`title_short` VARCHAR(30) NOT NULL,
	`url` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY (`league_id`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ngp_teams` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`team_id` INT UNSIGNED NOT NULL,
	`title` VARCHAR(255) NOT NULL,
	`url` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY (`team_id`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ngp_new_games` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `start_time` DATETIME DEFAULT NULL,
    `min` int DEFAULT NULL,
    `url` VARCHAR(255) NOT NULL,
    `league_short` VARCHAR(16) DEFAULT NULL,
    `league_url` VARCHAR(255) DEFAULT NULL,
    `host` VARCHAR(40),
    `host_rank` VARCHAR(10) DEFAULT NULL,
    `guest` VARCHAR(40),
    `guest_rank` VARCHAR(10) DEFAULT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ngp_games` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `start_time` DATETIME NOT NULL,
    `league_id` int UNSIGNED DEFAULT NULL,
    `host_id` int UNSIGNED NOT NULL,
    `host_rank` VARCHAR(10) DEFAULT NULL,
    `guest_id` int UNSIGNED NOT NULL,
    `guest_rank` VARCHAR(10) DEFAULT NULL,
    `state` INT DEFAULT NULL,    
    `trackable` TINYINT(1) DEFAULT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`),
    KEY (`trackable`),
    CONSTRAINT FK_games_league_id FOREIGN KEY (`league_id`) REFERENCES `ngp_leagues`(`league_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_games_host_id FOREIGN KEY (`host_id`) REFERENCES `ngp_teams`(`team_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_games_guest_id FOREIGN KEY (`guest_id`) REFERENCES `ngp_teams`(`team_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ngp_live_games` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `start_real` DATETIME NOT NULL,
    `state` INT NOT NULL,
    `trackable` TINYINT(1) DEFAULT NULL,
    `last_stat` JSON DEFAULT NULL,
    `next_update` DATETIME NULL DEFAULT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`),
    KEY (`trackable`),
    KEY (`start_real`),
    CONSTRAINT FK_live_games_game_id FOREIGN KEY (`game_id`) REFERENCES `ngp_games`(`game_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ngp_stats` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `team_id` INT UNSIGNED NOT NULL,
    `state` int not null,
    `min` int not null,
    `stat` json NOT NULL,
    `hash` char(10) not null COMMENT 'truncated SHA1 hash of stat',
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`, `team_id`, `state`, `hash`),
    CONSTRAINT FK_stats_game_id FOREIGN KEY (`game_id`) REFERENCES `ngp_games`(`game_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_stats_team_id FOREIGN KEY (`team_id`) REFERENCES `ngp_teams`(`team_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ngp_version` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `version` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;
INSERT INTO `ngp_version` (`version`) VALUES(2);

