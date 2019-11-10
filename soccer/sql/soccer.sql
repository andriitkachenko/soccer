-- drop table if exists `game_corrections`;
-- drop table if exists `games`;

-- truncate table `game_corrections`;
-- truncate table `games`;

create table if not exists `games` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `game_id` int(11) NOT NULL,
    `league` varchar(255) DEFAULT NULL,
    `start_at` TIMESTAMP NOT NULL,
    `host` varchar(255) NOT NULL,
    `host_rank` varchar(30) DEFAULT NULL,
    `guest` varchar(255) NOT NULL,
    `guest_rank` varchar(30) DEFAULT NULL,
    `finished` int(1) DEFAULT 0,
    `description` varchar(255) DEFAULT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`game_id`),
    KEY (`finished`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `game_corrections` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `game_id` int(11) NOT NULL,
    `current_time` TIMESTAMP NOT NULL,
    `game_time` varchar(8) NOT NULL,
    `first_half` int(1) NOT NULL,
    `score` varchar(30) NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`game_id`),
    UNIQUE KEY(`game_id`, `first_half`),
    CONSTRAINT FK_game_times_game_id FOREIGN KEY (`game_id`) REFERENCES `games`(`game_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARACTER SET=UTF8;

create table if not exists `games_version` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB, CHARACTER SET=UTF8;
s
-- INSERT INTO  `games_version` VALUES ();
