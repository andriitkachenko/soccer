-- drop table if exists `game_corrections`;
-- drop table if exists `game_events`;
-- drop table if exists `games`;

-- truncate table `game_corrections`;
-- truncate table `game_events`;
-- truncate table `games`;

create table if not exists `games` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `league` VARCHAR(30) DEFAULT NULL,
    `start_at` TIMESTAMP NOT NULL,
    `host` VARCHAR(255) NOT NULL,
    `host_rank` VARCHAR(30) DEFAULT NULL,
    `guest` VARCHAR(255) NOT NULL,
    `guest_rank` VARCHAR(30) DEFAULT NULL,
    `finished` TINYINT(1) DEFAULT 0 NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`),
    KEY (`finished`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `game_corrections` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `current_time` TIMESTAMP NOT NULL,
    `game_time` varchar(8) NOT NULL,
    `first_half` tinyint(1) NOT NULL,
    `score` varchar(30) NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`game_id`),
    UNIQUE KEY(`game_id`, `first_half`),
    CONSTRAINT FK_game_corrections_game_id FOREIGN KEY (`game_id`) REFERENCES `games`(`game_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `games_version` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;
-- INSERT INTO  `games_version` VALUES ();

create table if not exists `game_events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT UNSIGNED NOT NULL,
    `timestamp` TIMESTAMP NOT NULL,
    `host` tinyint(1) not null,
    `event` enum('gl', 'sg', 'sh', 'rc', 'yc', 'fl', 'bp', 'ck', 'of', 'hd', 'sv', 'st', 'ic', 'as') NOT NULL 
        COMMENT "
            gl = goals, 
            sg = shots on goal,
            sh = shots, 
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
            tm = treatments
        ",    
    `amount` TINYINT NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`, `host`, `event`, `amount`),
    CONSTRAINT FK_game_events_game_id FOREIGN KEY (`game_id`) REFERENCES `games`(`game_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
);

