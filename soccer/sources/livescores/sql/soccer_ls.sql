-- drop table if exists `game_corrections`;
-- drop table if exists `game_events`;
-- drop table if exists `games`;

-- truncate table `game_corrections`;
-- truncate table `game_events`;
-- truncate table `games`;

create table if not exists `ls_games` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `league` VARCHAR(255) DEFAULT NULL,
    `start_time` TIMESTAMP NOT NULL,
    `start_timestamp` INT UNSIGNED,
    `host` VARCHAR(255) NOT NULL,
    `guest` VARCHAR(255) NOT NULL,
    `finished` TINYINT(1) DEFAULT 0 NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`),
    KEY (`finished`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `ls_events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `min` INT NOT NULL,
    `extra` INT DEFAULT NULL,
    `host` TINYINT(1) NOT NULL,
    `event` enum('gl', 'sg', 'sh', 'rc', 'yc', 'fl', 'bp', 'ck', 'of', 'hd', 'sv', 'st', 'ic', 'gk', 'as') NOT NULL 
        COMMENT "
            gl = goals, 
            sg = shot on target (shot on goal),
            sh = shot off target, 
            rc = red cards,
            yc = yellow cards,
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

create table if not exists `cron_log` (
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

