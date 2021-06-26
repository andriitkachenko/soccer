<?php
declare(strict_types = 1);

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

function is_allowed_game($game) {
    $l = strtolower($g->league_short);
    $ht = strtolower($g->host);
    $gt = strtolower($g->guest);
    return 
        !in_array($l, NOT_ALLOWED_LEAGUES)
        && array_reduce(NOT_ALLOWED_LEAGUE_TEAM_NAMES, function($acc, $s) use($gt, $ht, $l) {
                return $acc 
                    && strpos($l, $s) === false
                    && strpos($ht, $s) === false
                    && strpos($gt, $s) === false;
            }, 
            true
        );
}
?>