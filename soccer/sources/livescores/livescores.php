<?php
declare(strict_types = 1);

require_once __DIR__ . '/../../db/db_utils.php';
require_once __DIR__ . '/parser.php';
require_once __DIR__ . '/curl.php';
require_once __DIR__ . '/../../log/log.php';
require_once __DIR__ . '/db_manager.php';

const GAME_LIST_URL = 'http://www.livescores.com';

/**
 *  update stat on full time
 *  goal minutes from match details
 *  delete old (>2 days) non-error cron logs 
 */
function runLivescores(DbManager $dbManager, Log &$log) {
    $ok = false;
    $list = getGameList(); 
    if ($list['ok']) {
        $log->setTrackableGames($list['count']);
        if ($list['count']) {
            $list = $list['list'];
            $dbResult = $dbManager->updateGames($list);
            if ($dbResult === true) {
                $log->append('Game list updated');
                $liveGames = getRunningGames($list);
                $log->setLiveGames(count($liveGames));
                if (!empty($liveGames)) {
                    $stats = getGamesStats($liveGames);
                    $log->setGamesWithStatistics(count($stats));
                    if (!empty($stats)) {
                        $dbResult = $dbManager->insertStats($stats);
                        if ($dbResult === true) {
                            $log->append('Games update completed OK');
                        } else {
                            $log->append($dbResult, LOG_TYPE_ERROR);
                        }
                    } else {
                        $log->append('Games update completed OK');
                    }
                } else {
                    $log->append('Games update completed OK');
                }
            } else {
                $log->append($dbResult, LOG_TYPE_ERROR);
            }
        }
    } else {
        $log->append("Game list parsing failure: " . $html, LOG_TYPE_ERROR);
    }

}

/**
 *  Only games with url
 */
function getGameList() {
    $y = date('Y');
    $m = date('m');
    $d = date('d');
    $html = curlGet(GAME_LIST_URL . "/soccer/$y-$m-$d/");
    $list = Parser::parseGameList($html);
    return $list === false 
        ? ['ok' => false, 'html' => $html]
        : ['ok' => true, 'count' => count($list), 'list' => $list];
}

function getRunningGames($games) {
    return array_filter($games, function ($g) {return !empty($g['time']['live']);});
}

function getGamesStats($runningGames) {
    $stats = [];
    foreach($runningGames as $g) {
        if (empty($g['url'])) {
            continue;
        }
        usleep(200000);
        $html = curlGet(GAME_LIST_URL . $g['url']);
        if (empty($html)) {
            updateCronLog('Stat URL error', GAME_LIST_URL . $g['url']);
            continue;
        }
        $stat = Parser::parseGameStat($html);
        if (empty($stat['stat'])) {
            continue;
        }
        if (!empty($stat['errors'])) {
            updateCronLog('Event parsing errors', implode('; ', $stat['errors']));
        }
        $stats[] = array_merge(
            [
                'game_id' => $g['id'],
            ],
            $stat['stat']
        );
    }
    return $stats;
}

?>