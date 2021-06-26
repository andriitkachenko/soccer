<?php
declare(strict_types=1);

require_once __DIR__ . '/../../php/logs.php';
require_once __DIR__ . '/../../services/parsehub/parsehub.php';
require_once __DIR__ . '/ngp_parsehub_config.php';
require_once __DIR__ . '/ngp_parser.php';
require_once __DIR__ . '/ngp_db_manager.php';
require_once __DIR__ . '/ngp_loader.php';
require_once __DIR__ . '/ngp_config.php';
require_once __DIR__ . '/ngp_utils.php';

interface iNowGoalPro {
    public function isParseHubClient() : bool;
    public function getParseHubGames($phData);
    public function runParseHubProject();
    public function runOneMinuteUpdate($stopTime);
}

class NowGoalPro implements iNowGoalPro {
    private $dbManager = null;
    private $loader = null;

    public function setDbManager(NgpDbManager $ngpDbManager) {
        $this->dbManager = $ngpDbManager;
        $this->loader = new NgpLoader();
    }
    
    public function isParseHubClient() : bool {
        return true;
    }

    public function updateNewGames($liveGames) {
        if (empty($this->dbManager)) {
            errorLog("updateNewGames", "DB manager not set");
            return false;
        }
        
        $liveGameIds = array_keys($liveGames);
        addLog('Live games: ' . count($liveGameIds));

        // get new game IDs
        $oldGameIds = $this->dbManager->getExistingGameIds($liveGameIds);

        $ok = $oldGameIds !== false;
        addLog(($ok ? 'Loaded ' .  count($oldGameIds) : 'Could not load') . ' existing game IDs.');
        if (!$ok) return false;

        $newLiveGameIds = array_values(array_diff($liveGameIds, $oldGameIds));

        // get new game IDs
        $goneGameIds = $this->dbManager->getGoneGameIds($liveGameIds); 

        $ok = $goneGameIds !== false;
        addLog(($ok ? 'Loaded ' .  count($goneGameIds) : 'Could not load') . ' gone game IDs.');
        if (!$ok) return false;

        addLog('Live: ' .  json_encode($liveGameIds));
        addLog('Old:  ' .  json_encode($oldGameIds));
        addLog('New:  ' .  json_encode($newLiveGameIds));
        addLog('Gone: ' .  json_encode($goneGameIds));

        $oldLiveGames = $this->dbManager->loadLiveGames();

        $ok = $oldLiveGames !== false;
        addLog(($ok ? 'Loaded ' .  count($oldLiveGames) : 'Could not load') . ' existing live games.');
        if (!$ok) return false;

        $newGames = array_filter($liveGames, function($liveGame, $id) use($newLiveGameIds) {
            return in_array($liveGame->id, $newLiveGameIds) 
                    && $liveGame->min > 1 
                    && $liveGame->min <= 45;
        }, ARRAY_FILTER_USE_BOTH);

        $ok = $this->dbManager->deleteNewGames($goneGameIds);

        addLog(($ok ? 'Deleted ' : 'Could not delete') . ' new games: ' . count($goneGameIds));
        if (!$ok) return false;

        $goneLiveGames = array_filter($oldLiveGames, function($g) use($goneGameIds) {
            return in_array($g->id, $goneGameIds);
        });

        $ok = $this->dbManager->untrackLiveGames($goneLiveGames);

        addLog(($ok ? 'Untracked ' : 'Could not untrack') . ' live games: ' . count($goneLiveGames));
        if (!$ok) return false;
        
        $ok = $this->dbManager->insertNewGames($newGames);

        addLog(($ok ? 'Added ' : 'Could not add') . ' new games: ' . count($newGames));

        return $ok;
    }

    public function runOneMinuteUpdate($stopTime = null, $maxGames = null) {
        addLog("runOneMinuteUpdate - start");
        if (empty($this->dbManager)) {
            errorLog("runOneMinuteUpdate", "DB manager not set");
            return false;
        }

        // read live games from DB which is trackable and need updating
        $trackableGameData = $this->dbManager->loadLiveTrackableGames();
        addLog('Loaded trackable games :' . count($trackableGameData));
        
        $updatedGames = $this->getStatsForTrackableGames($trackableGameData, $stopTime);
        addLog("Loaded stats for trackable games: " . count($updatedGames));
        
        $liveGames = array_filter($updatedGames, 
            function($g, $key) { return !empty($g) && !empty($g->status->live); }, 
            ARRAY_FILTER_USE_BOTH
        );
        $nonLiveGames = array_filter($updatedGames, 
            function($g, $key) { return !empty($g) && empty($g->status->live); }, 
            ARRAY_FILTER_USE_BOTH
        );
    
        $ok = $this->dbManager->updateLiveGames($liveGames);
        addLog(($ok ? 'Updated' : 'Could not update') . ' live trackable games.');
        if ($ok === false) return false;
        
        $ok = $this->dbManager->updateGames($nonLiveGames, ARCHIVED_NON_LIVE);
        addLog(($ok ? 'Archived' : 'Could not archive') . ' non-live games.');
        if ($ok === false) return false;
        
        $ok = $this->dbManager->deleteLiveGames($nonLiveGames);
        addLog(($ok ? 'Deleted' : 'Could not delete') . ' non-live games.');
        if ($ok === false) return false;

        addLog('Clean up live game list...');

        // archive finished and non-trackable games - at min 20 there is no meaningful stat
        $games = $this->dbManager->loadFinishedAndNonTrackableGames();
        addLog("Loaded non-trackable live games: " . count($games));
        addLog(json_encode($games));
        
        $ok = $this->dbManager->updateGames($games, ARCHIVED_AS_FINISHED_OR_NON_TRACKABLE);
        addLog(($ok ? 'Updated' : 'Could not update') . ' non-trackables in games table.');
        if ($ok === false) return false;

        $ok = $this->dbManager->deleteLiveGames($games);
        addLog(($ok ? 'Deleted' : 'Could not delete') . ' non-trackable live games.');
        if ($ok === false) return false;
        
        addLog('Clean up - OK');
        
        $newGames = $this->dbManager->loadNewGames($maxGames);
        addLog(($newGames !== false ? 'Loaded ' : 'Could not load') . ' new  games: ' . count($newGames));
        if ($ok === false) return false;
        
        $fullGames = $this->resolveNewGames($newGames, $stopTime);
        addLog('Resolved new games (with stat): ' . count($fullGames));
        
        $liveGames = array_filter($fullGames, function($g) {return $g->status->live;}); 
        addLog('New games to get tracked: ' . count($liveGames));
        
        $ok = $this->addLiveGames($liveGames);
        addLog(($ok ? 'Added' : 'Could not add') . ' live  games:' . count($liveGames));
        if ($ok === false) return false;
        
        $resolvedGameIds = array_keys($fullGames);
        $ok = $this->dbManager->deleteNewGames($resolvedGameIds);
        addLog(($ok ? 'Deleted' : 'Could not delete') . ' resolved new  games:' . count($resolvedGameIds));
        
        return $ok;
    }
    
    private function addLiveGames($games) {
        $leagues = array_map(function($g) {return $g->league;}, $games);
        $ok = $this->dbManager->insertLeagues($leagues);
        addLog(($ok ? 'Inserted' : 'Could not insert') . ' leagues:' . count($leagues));
        if (!$ok) return false;
        
        $teams = [];
        foreach($games as $g) {
            $teams[] = $g->host;
            $teams[] = $g->guest;
        }
        $ok = $this->dbManager->insertTeams($teams);
        addLog(($ok ? 'Inserted' : 'Could not insert') . ' teams:' . count($teams));
        if (!$ok) return false;
        
        $ok = $this->dbManager->insertLiveGames($games);
        addLog(($ok ? 'Inserted' : 'Could not insert') . ' live games:' . count($games));
        
        return $ok; 
    }

    public function getStatsForTrackableGames($gameData, $stopTime) {
        if (empty($this->dbManager) || empty($gameData) || !is_array($gameData)) {
            return [];
        }
        return $this->loader->loadMultiGameStats($gameData, $stopTime);
    }
    
    public function resolveNewGames($newGames, $stopTime) {
        if (empty($newGames)) return [];
        // update new games
        $stats = $this->loader->loadMultiGameStats($newGames, $stopTime);
        if (empty($stats)) return [];

        $fullGames = [];
        foreach($newGames as $id => $g) {
            if (!empty($stats[$id])) {
                $fullGame = $this->makeFullGame($g, $stats[$id]);
                $fullGames[$id] = $fullGame;
            }
        }

        return $fullGames;
    }    

    private function makeFullGame($game, $stat) {
        if ($game->id != $stat->status->game_id) {
            errorLog("makeFullGame", "IDs do not match");
            return false;
        }
        $stat->league = (object)array_merge((array)$stat->league, ['title_short' => $game->league_short]);
        $stat->host = (object)array_merge((array)$stat->host, ['rank' => $game->host_rank]);
        $stat->guest = (object)array_merge((array)$stat->guest, ['rank' => $game->guest_rank]);
        $g = array_merge([
                'id' => $game->id,
                'url' => $game->url
            ],
            (array)$stat
        );
        return (object)$g;
    }

    public function runParseHubProject() {
        $ph = new ParseHub(PH_PROJECT_TOKEN, PH_API_KEY);
        return $ph->runProject();
    }

    public function getParseHubGames($phData) {
        if (empty($phData->game) || !is_array($phData->game)) {
            return [];
        }
        $games = [];
        foreach($phData->game as $g) {
            if (empty($g->id) || empty($g->html)) {
                continue;
            }
            $id = str_replace('tb_', '', $g->id);
            $game = NGPParser::parseGame($g->html);            
            if (empty($game)) {
                errorLog("getParseHubGames", NGPParser::getLog());
            } else if (is_allowed_game($game)) {
                $games[$game->id] = $game;
            }
        }
        return $games;
    }
}
?>