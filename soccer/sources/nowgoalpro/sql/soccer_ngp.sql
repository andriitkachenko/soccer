-- drop table if exists `game_corrections`;
-- drop table if exists `game_events`;
-- drop table if exists `games`;

-- truncate table `game_corrections`;
-- truncate table `game_events`;
-- truncate table `games`;

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


create table if not exists `ngp_live_games` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `league_id` int DEFAULT NULL,
    `start_time` DATETIME NOT NULL,
    `start_real` DATETIME NOT NULL,
    `host_id` int NOT NULL,
    `host_rank` VARCHAR(10),
    `guest_id` int NOT NULL,
    `guest_rank` VARCHAR(10),
    `trackable` TINYINT(1) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`),
    KEY (`trackable`),
    KEY (`start_real`),
    CONSTRAINT FK_games_league_id FOREIGN KEY (`league_id`) REFERENCES `ngp_leagues`(`league_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_games_host_id FOREIGN KEY (`host_id`) REFERENCES `ngp_teams`(`team_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_games_guest_id FOREIGN KEY (`guest_id`) REFERENCES `ngp_teams`(`team_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ngp_finished_games` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `league_id` int DEFAULT NULL,
    `start_time` DATETIME NOT NULL,
    `host_id` int NOT NULL,
    `host_rank` VARCHAR(10) NOT NULL,
    `guest_id` int NOT NULL,
    `guest_rank` VARCHAR(10) NOT NULL,
    `trackable` TINYINT(1) DEFAULT 1 NOT NULL,
    `description` TEXT DEFAULT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`),
    KEY (`trackable`),
    KEY (`start_real`),
    CONSTRAINT FK_games_league_id FOREIGN KEY (`league_id`) REFERENCES `ngp_leagues`(`league_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_games_host_id FOREIGN KEY (`host_id`) REFERENCES `ngp_teams`(`team_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_games_guest_id FOREIGN KEY (`guest_id`) REFERENCES `ngp_teams`(`team_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARACTER SET=UTF8;


create table if not exists `ngp_teams` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `team_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`team_id`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ngp_leagues` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `league_id` INT NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `url` VARCHAR(255) NOT NULL,
    `short` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`league_id`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ngp_events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `min` INT NOT NULL,
    `extra` INT DEFAULT NULL,
    `host` TINYINT(1) NOT NULL,
    `event` enum('gl', 'sg', 'sh', 'rc', 'yc', 'fl', 'bp', 'at', 'da', 'ck', 'of', 'hd', 'sv', 'st', 'ic', 'gk', 'as') NOT NULL 
        COMMENT "
            gl = goals, 
            sg = shot on goal,
            sh = shots, 
            rc = red cards,
            yc = yellow cards,
            at = attack,
            da = dangerous attack,
            fl = fouls,
            bp = ball possession,
            ck = corner kicks,
            of = offsides,
            hd = headers,
            sv = saves,
            st = successful tackles,
            ic = interceptions,
            as = assists,
            gk = goal kick,
            tm = treatments
        ",    
    `amount` TINYINT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`, `host`, `event`, `amount`),
    KEY (`event`),
    KEY (`min`),
    CONSTRAINT FK_game_events_game_id FOREIGN KEY (`game_id`) REFERENCES `ls_games`(`game_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ngp_logs` (
    `id` int unsigned not null AUTO_INCREMENT,
    `error` tinyint(1) not null,
    `trackable_games` int default null,
    `live_games` int default null,
    `games_with_statistics` int default null,
    `log` mediumtext  not null,
    `timestamp` TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(id),
    KEY (`error`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;

