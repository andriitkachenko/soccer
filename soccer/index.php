<?php
    const DATA_FILE = __DIR__ . '/data/games.txt';
    $games = [];
    if (file_exists(DATA_FILE)) {
        $fileDate = filemtime(DATA_FILE);
        if ($fileDate > mktime(0, 0, 0) && $fileDate < mktime(23, 59, 59)) {
            $games = file_get_content(DATA_FILE);
        }
    }
    $games = json_encode($games);
    if ($games === FALSE) {
        $games = '{}';
    }
?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <link rel="stylesheet" href="css/main.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="js/config.js"></script>
        <script src="js/crossdomainajax.js"></script>
        <script src="js/data.js"></script>
        <script src="js/statistics.js"></script>
        <script src="js/time.js"></script>
        <script src="js/team.js"></script>
        <script src="js/match.js"></script>
        <script src="js/manager.js"></script>
        <script src="js/livebet.js"></script>
    </head>
    <body onload="onLoad()">
        <div id='main_div'>
            <div id="header">
                <table>
                    <tr>
                        <td rowspan="2"><button type="button" id="startButton" onclick="turnWatching()">Start</button></td>
                        <td colspan="4" width=50%><input type="text" id='inputMatch' style="width:100%"></td>
                        <td rowspan="2"><button type="button" onclick="sortByTime()">Time</button></td>
                        <td rowspan="2"><button type="button" onclick="sortByHalfShots()">Half</button></td>
                        <td rowspan="2"><button type="button" onclick="clearList()">Clear</button></td>
                    </tr>
                    <tr>
                        <td><input type="time" id='startTime' step='300' min="00:00" max="23:55"></td>                
                        <td> <input type="number" id='timeCorrection' step="1" min="-10" max="10" value="0"></td>
                        <td> <button type="button" onclick="addNewMatch()">Apply</button></td>
                        <td><button type="button" id="notification" onclick="changeNotifications()">Notif</button></td>
<!--
                        <td><button type="button" onclick="sortByMatchShots()">Match</button></td>
                        <td><button type="button" onclick="sortByLast()">Last</button></td>
-->
                    </tr>
                </table>
            </div>
            <div id="view_body">
                <div id="list_placeholder"></div>
                <div id='matchlist'></div>
                <div id="list_placeholder"></div>
            </div>
        </div>
    </body>
</html>
