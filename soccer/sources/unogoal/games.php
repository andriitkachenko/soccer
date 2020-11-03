<?php

const NOT_ALLOWED_LEAGUES = [
    "fut ifi",
    "jap futl",
    'rus fdh',
    'spa fdn',
    'estonia wt',
    'afc fc',
    'bswcp',
    'labsc'
];

const NOT_ALLOWED_LEAGUE_TEAM_NAMES = [
    "futsal",
    "indoor"
];

function filterAndPrepairGames($games) {
    return prepairGames(filterGames($games));
}

function parseAndFilterGames($json) {
    $games = json_decode($json, true);
    return filterGames($games);
}

function normilizeGameTime($time) {
    return trim(strtolower($time));
}

function splitHostName($team) {
    $hostRankExists = preg_match('/^(\[[^\]]+\])/', $team, $matches);
    $hostRank = $hostRankExists ? $matches[0] : "";
    if (!empty($hostRank)) {
        return [ 
            trim(substr($team, strlen($hostRank))), 
            trim($hostRank)
        ];
    }
    return [$team, ''];
}

function splitGuestName($team) {
    $guestRankExists = preg_match('/(\[[^\]]+\])$/', $team, $matches);
    $guestRank = $guestRankExists ? $matches[0] : "";
    if (!empty($guestRank)) {
        return [
            trim(substr($team, 0, strlen($team) - strlen($guestRank))),
            trim($guestRank)
        ];
    }
    return [$team, ''];
}

function prepairGames($games) {
    if (empty($games)) {
        return [];
    }
    $prepaired = [];
    foreach($games as $g) {
        $g['game_time'] = normilizeGameTime($g['game_time']);
        list($host, $hostRank) = splitHostName($g['host']);
        list($guest, $guestRank) = splitGuestName($g['guest']);
        $g['host'] = $host;
        $g['guest'] = $guest;
        $g['host_rank'] = $hostRank;
        $g['guest_rank'] = $guestRank;
        $prepaired[] = $g;
    }
    return $prepaired;
}

function filterGames($games) {
    if (empty($games)) {
        return [];
    }
    $filtered = [];
    foreach($games as $g) {
        $wrongLeague = in_array($g['league'], NOT_ALLOWED_LEAGUES);
        if ($wrongLeague) 
            continue;
        $wrongTeamNames = 0 !== array_sum(
            array_map(function ($name) use ($g) {
                return +(strpos($g['host'], $name) !== false || strpos($g['guest'], $name) !== false);
            }, 
            NOT_ALLOWED_LEAGUE_TEAM_NAMES
        ));
        if ($wrongTeamNames) 
            continue;
        $filtered[] = $g;
    }
    return $filtered;
}

function getGames() {
    return getGamesFromFile();
}

function getGamesFromDB() {
// [{"id":1650887,"league":"PAR D1","start_time":26221995,"host":"Guarani CA","host_rank":"[4]","guest":"Deportivo Capiata","guest_rank":"[9]"} 
    return readNonFinishedGames();
}
function getGamesFromFile() {
// [{"id":"1810907","league":"ENG FAC","start_time":"15:00","game_time":"46","host":"Accrington Stanley","guest":"Crewe Alexandra","score":"0 - 1","host_rank":"[ENG L1-19]","guest_rank":"[ENG L2-4]"} ...
    $games = '';
    $fileDate = null;
    if (file_exists(DATA_FILE)) {
        $fileDate = filemtime(DATA_FILE);
        if ($fileDate > mktime(0, 0, 0) && $fileDate < mktime(23, 59, 59)) {
            $games = file_get_contents(DATA_FILE);
        }
    }
    $games = json_decode($games, true);
    if ($games === FALSE) {
        $games = [];
    }
    return [
        'data' => json_encode($games),
        'timestamp' => $fileDate
    ];
}
?>