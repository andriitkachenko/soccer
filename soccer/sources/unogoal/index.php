<?php
    require_once __DIR__ . '/php/logs.php';
    require_once __DIR__ . '/php/games.php';

    accessLog();
    list($games, $timestamp) = isset($_GET['db']) ? getGamesFromDB() : getGamesFromFile();

    function makeVersionedFilePath($filePath) {
        if (!file_exists($filePath)) {
            return $filePath;
        }
        return $filePath . '?v=' . filemtime($filePath);
    }
?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <!-- Add icon library -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        
        <link rel="stylesheet" href="<?= makeVersionedFilePath('css/main.css'); ?>">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="<?= makeVersionedFilePath('js/config.js'); ?>"></script>
        <script src="<?= makeVersionedFilePath('js/data.js');?>"></script>
        <script src="<?= makeVersionedFilePath('js/statistics.js');?>"></script>
        <script src="<?= makeVersionedFilePath('js/time.js');?>"></script>
        <script src="<?= makeVersionedFilePath('js/team.js');?>"></script>
        <script src="<?= makeVersionedFilePath('js/match.js');?>"></script>
        <script src="<?= makeVersionedFilePath('js/manager.js');?>"></script>
        <script src="<?= makeVersionedFilePath('js/livebet.js');?>"></script>
        <script>
            const GAME_LIST_TIME = '<?= $games['timestamp'] ? date('d M H:i', $games['timestamp']) : "" ?>';
            const GAMES = '<?= $games['data'] ?>';

            window.onbeforeunload = function(e) {
                var dialogText = 'Game data will be lost on page reload';
                onUnload();
                e.returnValue = dialogText;
                return dialogText;
            };
        </script>
    </head>
    <body onload="onLoad()">
        <div id='main_div'>
            <div id="header">
                <table>
                    <tr>
                        <td><button type="button" id="startButton" onclick="toggleWatching()">Start</button></td>
                        <td><button type="button" onclick="openDataPage()">Data</button></td>
                        <td><button type="button" id="notification" onclick="changeNotifications()">Notif</button></td>
                        <td><input type="number" id='matchTime' step="1" min="1" max="99"></td>
                        <td>
                            <button id="sort" class="btn" onclick="toggleSort()">
                                <i class="fa fa-sort-amount-desc" aria-hidden="true"></i>
                            </button>
                        </td>
                        <td>
                            <button id="filter" class="btn" onclick="toggleFilter()">
                                <i class="fa fa-filter" aria-hidden="true"></i>
                            </button>
                        </td>
                        <td>
                            <button id="clear" class="btn" onclick="clearList()">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
<!--
                    <tr>
                        <td><input type="time" id='startTime' step='300' min="00:00" max="23:55"></td>                
                        <td> <input type="number" id='timeCorrection' step="1" min="-10" max="10" value="0"></td>
                        <td><button type="button" onclick="sortByMatchShots()">Match</button></td>
                        <td><button type="button" onclick="sortByLast()">Last</button></td>
                    </tr>
-->
                </table>
            </div>
            <div id="view_body">
                <div id='matchlist'></div>
            </div>
            <div id="footer">
                <div id="list_info"></div>
            </div>
        </div>
    </body>
</html>
