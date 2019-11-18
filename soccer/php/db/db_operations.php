<?php

require_once __DIR__ . '/db_utils.php';
require_once __DIR__ . '/../logs.php';
require_once __DIR__ . '/../utils.php';

const GAMES_FIELDS = [
    'game_id', 
    'league', 
    'start_at', 
    'host', 
    'host_rank', 
    'guest', 
    'guest_rank', 
    'finished', 
    'description'
];
const GAME_TIMES_FIELDS = [
    'game_id', 
    'current_time', 
    'game_time', 
    'first_half', 
    'score'
];

function getGameMinute($time) {
    $mins = @intval($time);
    $ok = $mins > 1 || $mins == 1 && $time == '1';
    return $ok ? $mins : null;
}

function getGameStatus($time) {
    $running = in_array($time, ['45+', '90+', 'ht', 'ot', 'part1', 'part2', 'nan']);
    if (empty($time) || $running || getGameMinute($time)) {
        return [0, ''];
    } 
    if ($time == 'ft') {
        return [1, ''];
    }
    return [1, $time];
}

function constGameParams2value($game) {
    $id = $game['id'];
    $league =  $game['league'];
    $league = empty($league) ? 'NULL' : "'$league'";
    $start_time = DateTime::createFromFormat('H:i', $game['start_time'])->format('Y-m-d H:i:00');
    $host = $game['host'];
    $guest = $game['guest'];
    $hostRank = $game['host_rank'];
    $hostRank = empty($hostRank) ? 'NULL' : "'$hostRank'";
    $guestRank = $game['guest_rank'];
    $guestRank = empty($guestRank) ? 'NULL' : "'$guestRank'";
    list($finished, $descr) = getGameStatus($game['game_time']);
    $descr = empty($descr) ? 'NULL' : "'$descr'";
    return "($id, $league, '$start_time', '$host', $hostRank,  '$guest',  $guestRank, $finished, $descr)";
}

function varGameParams2value($game, $ahchorTime) {
    $time = @intval($game['game_time']);
    $score = $game['score'];
    if ($time <= 1 || empty($score)) {
        return false;
    }
    $id = $game['id'];
    $cur_time = date('Y-m-d H:i:s', $ahchorTime);
    $game_time = $time;
    $firstHalf = $time <= 45 ? 1 : 0;
    return "($id, '$cur_time', '$game_time', $firstHalf, '$score')";
}

function saveGamesToDB($games, $ahchorTime) {
    $conn = makeConnection();
    if (empty($conn)) {
        return false;
    }    
    $gameIds = array_map(function($g) {return $g['game_id'];}, $games);
    // get all unfinished
    $oldNotFinishedGameIds = loadOldNotFinishedGameIds($conn, $ahchorTime);
    // mark as finished all that absent in the game list
    $oldGamesIds = array_diff($oldNotFinishedGameIds, $gameIds);
    $res = $res && markGamesAsFinished($conn, $oldGamesIds, $ahchorTime);
    // update games in the db
    $res = $res && insertConstGameParams($conn, $games) && insertVarGameParams($conn, $games, $ahchorTime);
    // update version
    $res = $res && updateGameListVersion($ahchorTime);
    // wrap it up
    closeDbConnection($conn);
    return $res;
}

function  markGamesAsFinished($conn, $absentGamesIds, $anchorTime) {
    if (empty($absentGamesIds)) {
        return true;
    }
    $gameIdList = implode(',', $absentGamesIds);
    $query = 
        "UPDATE `games`
            SET `finished` = 1
            WHERE `finished` = 0 AND `game_id` IN ($gameIdList);
        ";
    return exec_query($conn, $query);
}

function updateGameListVersion($conn, $anchorTime) {
    $timestamp = date('Y-m-d H:i:s', $ahchorTime);
    $query = 
        "UPDATE `games_version`
            SET `last_update` = $timestamp 
            LIMIT 1;
        ";
    return exec_query($conn, $query);
}

function insertConstGameParams($conn, $games) {
    if (!count($games)) {
        return true;
    }
    $values = [];
    foreach($games as $g) {
        $values[] = constGameParams2value($g);
    }
    $values = implode($values, ', ');
    $fields = implode(array_map(function($f) {return "`$f`";}, GAMES_FIELDS), ', ');
    $duplicates = implode(array_map(function($f) {return "`$f`=VALUES(`$f`)";}, GAMES_FIELDS), ', ');
    $query = 
        "INSERT INTO `games` ($fields) 
            VALUES $values 
            ON DUPLICATE KEY UPDATE $duplicates;
        ";
    return exec_query($conn, $query);  
}

function insertVarGameParams($conn, $games, $ahchorTime) {
    if (!count($games)) {
        return true;
    }
    $values = [];
    foreach($games as $g) {
        $value = varGameParams2value($g, $ahchorTime);
        if ($value) {
            $values[] = $value;
        }
    }
    $values = implode($values, ', ');
    $fields = implode(array_map(function($f) {return "`$f`";}, GAME_TIMES_FIELDS), ', ');
    $duplicates = implode(array_map(function($f) {return "`$f`=VALUES(`$f`)";}, GAME_TIMES_FIELDS), ', ');
    $lastUpdate = date('Y-m-d H:i:s', $ahchorTime);
    $query = 
        "INSERT INTO `game_corrections` ($fields) 
            VALUES $values 
            ON DUPLICATE KEY UPDATE $duplicates;
        UPDATE `games_version` 
            SET `last_update`= $lastUpdate
            LIMIT 1;
        ";
    return exec_query($conn, $query);  
}

function loadOldNotFinishedGameIds($conn, $anchorTime) {
    $time = date('Y-m-d H:i:00', $anchorTime - 30 * 60);
    $query =
       "SELECT `game_id`
        FROM `games` 
        WHERE `finished` = 0 AND `start_at` < $time;
    ";
    $rows = $conn->query($query)->fetchAll();
    if ($rows === false) {
        return false;
    }
    $ids = [];
    foreach ($rows as $r) {
        $id =  @intval($r['game_id']);
        if ($game['id'] <= 1)
            continue;
        $ids[] = $id;
    }
    return $ids;
}

function loadNotFinishedGames($conn) {
    $query =
       "SELECT 
            `game_id`, 
            IFNULL(`league`, '') league, 
            `start_at`, 
            `host`, 
            IFNULL(`host_rank`, '') host_rank, 
            `guest`, 
            IFNULL(`guest_rank`, '') guest_rank
        FROM `games` 
        WHERE `finished` = 0
    ";
    $rows = $conn->query($query)->fetchAll();
    if ($rows === false) {
        return false;
    }
    $games = [];
    foreach ($rows as $r) {
        $game = [];
        $game['id'] =  @intval($r['game_id']);
        if ($game['id'] <= 1)
            continue;
        $game['league']     =  $r['league'];
        $game['start_at']   =  $r['start_at'];
        $game['start_time'] =  getMinuteTimestamp(DateTime::createFromFormat('Y-m-d H:i:00', $r['start_at']));
        $game['host']       =  $r['host'];
        $game['host_rank']  =  $r['host_rank'];
        $game['guest']      =  $r['guest'];
        $game['guest_rank'] =  $r['guest_rank'];
        $games[] = $game;
    }
    return $games;
}

function readNotFinishedGames() {
    $conn = makeConnection();
    if (empty($conn)) {
        return false;
    }    
    $games = loadNotFinishedGames($conn);
    closeDbConnection($conn);
    return $games;
}
?>
