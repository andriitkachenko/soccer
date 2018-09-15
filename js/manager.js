function MatchManager() {
    var matches = [];
    const updateViewInterval = 30.; // seconds
    var interval;

    function start() {
        updateList();
        inteval = setInterval(updateList, updateViewInterval * 1000);
    }

    function findMatchIndex(id) {
        var idx = matches.findIndex(function(m){
            return m.getMatchId() == id;
        });
        return idx;
    }

    function matchExists (id){
        return findMatchIndex(id) != -1;
    }

    this.matchExists = function (id) {
        return matchExists(id);
    }

    this.getMatch = function(id) {
        var idx = findMatchIndex(id);
        var match = undefined;
        if (idx != -1) {
            match = matches[idx];
        }
        return match;
    }

    this.removeMatch = function (id) {
        var idx  = findMatchIndex(id);
        if (idx != -1) {
            var m = matches[idx];
            m.setWatch(false);
            matches.splice(idx, 1);
            updateList();
        }
    }

    function addMatch(game) {
        var m = new Match(game);
        matches.push(m);
    }

    this.addMatch = function (game) {
        return addMatch(game);
    }

    this.setMatches = function(list) {
        var match = {};
        list.forEach(function(game) {
            if (matchExists(game['id'])) {
                // already in the list
                matches[findMatchIndex(game['id'])].update(game);
                return;
            }
            addMatch(game);
        });
        start();
    }

    this.updateList = function() {
        updateList();
    }

    this.updateStatus = function() {
        var delay = 0;
        for (var i = 0; i < currentList.length; i++) {
            setTimeout(currentList[i].updateStatus, delay);
            delay += 400;
        };
        setTimeout(makeView, delay + 1000);
    }

    this.clearList = function() {
        var time = getTimestamp();
        var indexes = [];
        for (var i = 0; i < matches.length; i++) {
            var m = matches[i];
            if (m.isWatching()) {
                var mTime = m.getMatchTime(time);
                var stats = m.makeMatchStats(time);
                var s = stats['host']['match'];
                // TODO check in team
                var nonEmptyStat = s && (s.hasOwnProperty('sh') || s.hasOwnProperty('bp'));
                if (mTime > MIN_CLEAR_TIME && !nonEmptyStat) {
                    indexes = [i].concat(indexes);
                }
            }
        }
        for (var i = 0; i < indexes.length; i++) {
            var idx = indexes[i];
            matches[idx].setWatch(false);
            matches.splice(idx, 1);
        }
        updateList();
    }

//
//
//

    var currentList = [];
    var sortFunction = sortByRealStartTime;


    function updateList (removeNonTracked = false) {
        if (currentList.length > 0 && !allowGlobalUpdate()) {
            return;
        }
        var list = [];
        var time = getTimestamp();
        for (var i = 0; i < matches.length; i++) {
            var m = matches[i];
            var mtime = m.getMatchTime(time);
            var maxTime = m.isWatching() ? MAX_WATCHING_TIME : MAX_START_WATCHING_TIME;
            if (mtime >= MIN_WATCHING_TIME && mtime <= maxTime) {
                if (!m.isWatching()) {
                    m.setWatch(true);
                }
                list.push(m); 
                if (dev_mode && list.length == 10) {
                    i = matches.length;
                }
            } else if (m.isWatching()) {
                m.setWatch(false);
            }
        }
        currentList = list.sort(function(a, b) {
            return sortFunction(time, a, b);
        });
        makeView();
    }

    function isStatOk(stat) {
        return stat && stat['host'] && stat['guest'];
    }

    function extractStatType(stat, math, part, type) {
        var sh = 0;
        var ok = isStatOk(stat) && stat['host'][part] && stat['guest'][part];
        if (ok && !isNaN(stat['host'][part][type]) && !isNaN(stat['guest'][part][type])) {
            sh = math == 'max' 
                ? Math.max(stat['host'][part][type], stat['guest'][part][type]) 
                : Math.min(stat['host'][part][type], stat['guest'][part][type]);
        }
        return sh;
    }

    function extractMaxStatType(stat, part, type) {
        return extractStatType(stat, 'max', part, type);
    }

    function extractMinStatType(stat, part, type) {
        return extractStatType(stat, 'min', part, type);
    }

    function getMaxStatType(time, match, part, type) {
        var stat = match.makeMatchStats(time);
        return extractMaxStatType(stat, part, type);
    }

    function getMatchShots(time, match) {
        return getMaxStatType(time, match, "match", "sh");
    }

    function getHalfShots(time, match) {
        var half = 'half1';
        if (match.getMatchTime(time) > 45) {
            half = 'half2';
        }
        return getMaxStatType(time, match, half, "sh");
    }

    function getLastShots(time, match) {
        return getMaxStatType(time, match, 'last15', "sh");
    }
    
    function sortByMatchShots(time, a, b) {
        var s = getMatchShots(time, b) - getMatchShots(time, a);
        return s == 0 ? a.getMatchId() - b.getMatchId() : s;
    }

    function sortByHalfShots(time, a, b) {
        if (a.isMatchBreak(time)) {
            return 1;
        }
        if (b.isMatchBreak(time)) {
            return -1;
        }
        var s = getHalfShots(time, b) - getHalfShots(time, a);
        return s == 0 ? a.getMatchId() - b.getMatchId() : s;
    }

    function sortByLastShots(time, a, b) {
        var s = getLastShots(time, b) - getLastShots(time, a);
        return s == 0 ? a.getMatchId() - b.getMatchId() : s;
    }

    function sortByRealStartTime(time, a, b) {
        var s = a.getRealStartTime() - b.getRealStartTime();
        return s == 0 ? a.getMatchId() - b.getMatchId() : s;
    }

    this.sortListByHalfShots = function() {
        sortFunction = sortByHalfShots;
    }

    this.sortListByMatchShots = function() {
        sortFunction = sortByMatchShots;
    }

    this.sortListByMatchTime = function() {
        sortFunction = sortByRealStartTime;
    }

    this.sortListByLastShots = function() {
        sortFunction = sortByLastShots;
    }

    function checkStats(stats) {
        if (!stats['time']) {
            return false;
        }
        var half1 = stats['time'] < 46;
        var half2 = !half1;
        var half = 'half' + (half1 ? '1' : '2');
        var halfTime = stats['time'] == 45;
        var startTime = stats['time'] < 20 || (half2 && stats['time'] < 60); 
        var endTime = stats['time'] > 85 || stats['time'] > 40; 
        return !halfTime 
            && !startTime 
            && !endTime
            && (
                    (
                        half1
                        && stats['time'] < 40
                        && stats['time'] / extractMaxStatType(stats, 'half1', 'sh') <= 4.5
                    )
                    || 
                    (
                        half2
                        && stats['time'] < 85
                        && (stats['time'] - 45) / extractMaxStatType(stats, 'half2', 'sh') <= 4.5
                    )
                    || 
                    (
                        extractMaxStatType(stats, half, 'sh')  - extractMinStatType(stats, half, 'sh') > 6
                    )
                    || 
                    (
                        extractMaxStatType(stats, 'last15', 'sh') >= 5
                        && extractMaxStatType(stats, 'last15', 'sg') >= 2
                    )

             )
    }

///
/// view    
///

function makeView() {
    updateListView(makeListView(currentList));
}

function makeListView(matchList) { 
        var list = '';
        var time = getTimestamp();
        for(var i=0; i < matchList.length; i++) {
            var m = matchList[i];
            var stats = m.makeMatchStats(time);
            if (checkStats(stats)) {
                showNotification(stats);
            }
            list += makeMatchView(stats);
        };
        return list;
    }

    function makeShotStatString(stats, part, team) {
        var undef = ".";
        if (!(stats && stats[team] && stats[team][part])) {
            return undef;
        }
        var part = stats[team][part];
        var sg = !isNaN(part["sg"]) ? part['sg'] : undef;
        var sh = !isNaN(part["sh"]) ? part['sh'] : undef;
        return sh + " - " + sg;
    }

   function makeStatString(stats, part, key) {
       var str = "--";
       if (stats['host'] && stats['host'][part] && !isNaN(stats['host'][part][key])) {
            if (key != "bp") {
                str = stats['host'][part][key] + " - " + stats['guest'][part][key];
            } else {
                str = "" + stats['host'][part][key] - stats['guest'][part][key];
            }
       }
        return  str;
    }

    function exists (stats, part) {
        return stats && stats['host'] && stats['guest'] && stats['host'][part] && stats['guest'][part];
    }

    function makeGoalString(stats, part) {
        var goals = '. - .';
        if (exists(stats, part)) {
            var h = stats['host'][part];
            var g = stats['guest'][part];
            goals = !isNaN(h['gl']) && !isNaN(g['gl']) ? h['gl'] + " - " + g['gl'] : goals;
        }
        return goals;
    }

    function makeRow30(stats, team) {
    /*
        0 - fail
        1 - no event
        2 - red card
        3 - shot
        4 - shot on goal
        5 - goal
    */ 
        
        var row = '';
        var e30 = stats[team]['events30'];
        for (var i = 0; i < 30; i++) {
            var text = '';
            var color = 'white';
            var event = (e30 && e30[i] && e30[i]['event']) ? e30[i]['event'] : 0;
            switch(event) {
                //fail
                case 0 : text = 'x'; break;
                //no change
                case 1 : text = '-'; break;
                // red card
                case 2 : color = 'red'; break;
                // shot
                case 3 : color = 'orange'; break;
                // shot on goal
                case 4 : color = 'green'; break;
                // goal
                case 5 : color = 'blue'; break;
            }
            row += '<td class="event" >\
                <div class="event" style="background-color:' + color + ';">' + text + '</div>\
                </td>';
        }
        return '<tr>' + row + '</tr>';
    }

    function makeRow310(stats, team) {
        /*
            0 - fail
            1 - no event
            2 - red card
            3 - shot
            4 - shot on goal
            5 - goal
        */ 
            
            var time = stats['time']; // 0 - 90
            var row = '';
            var e30 = stats[team]['events30'];
            for (var i = 1; i <= 30; i++) {
                var text = '';
                var style = '';
                var min = time - 30 + i; // 0- 90
                var minTime = time > 45 ? 46 : 1;
                if (e30 && min > minTime) {
                    var event = -1; // fail
                    for (var j = 0; j < e30.length && event == -1; j++) {
                        if (e30[j]['time'] == min){
                            event = e30[j]['event'];
                        }
                    }
                    if (event == -1) {
                        text = 'x';
                    } else {
                        var color = 'white';
                        switch(event) {
                            case 0 : text = '-'; break;
                            case 1 : color = 'red'; break;
                            case 2 : color = 'orange'; break;
                            case 3 : color = 'green'; break;
                            case 4 : color = 'blue'; break;
                        }
                        style = 'background-color:' + color + ';';
                    }
                }
                row += '<td class="event" >\
                    <div class="event" style="' + style + '">' + text + '</div>\
                    </td>';
            }
            return '<tr>' + row + '</tr>';
        }
    
    
    function makeMatchView(stats) {
        var view = 
        '<div id="%ID%" style="text-align:center">\
            <table id="matchinfo">\
                <tr>\
                    <td rowspan="2"><button class="close" type="button" onclick="removeMatch(%ID%)">X</button></td>\
                    <td style="font-size:0.7em;">%LEAGUE%</td>\
                    <td rowspan="2" style="font-size:1.2em;" onclick="setMatchPanel(%ID%, %TIME%)"><b>%TIME%</b></td>\
                    <td style="text-align:left;" width=40%><b>%hostName%</b>&nbsp;%hostRank%</td>\
                    <td rowspan="2"><b>%GOALS_MATCH%</b></td> \
                </tr>\
                <tr>\
                    <td style="font-size:0.7em;">%START_TIME%</td> \
                    <td style="text-align:left;" width=40%><b>%guestName%</b>&nbsp;%guestRank%</td>\
                </tr>\
            </table>\
            <table id="matchstat" cellspacing="0"\
                <tr>\
                    <td class="match_part">M</td>\
                    <td class="shots">%7%</td>\
                    <td class="shots">%8%</td>\
                    <td class="ball_posession">%9%</td>\
                    <td class="match_part">H1</td>\
                    <td class="shots">%10%</td>\
                    <td class="shots">%11%</td>\
                    <td class="ball_posession">%12%</td>\
                    <td class="match_part">H2</td>\
                    <td class="shots">%13%</td>\
                    <td class="shots">%14%</td>\
                    <td class="ball_posession">%15%</td>\
                    <td class="match_part">L%last%</td>\
                    <td class="shots">%16%</td>\
                    <td class="shots">%17%</td>\
                    <td class="ball_posession">%18%</td>\
                </tr>\
            </table>\
            <table id="matchevents" cellspacing="0">'
            + makeRow30(stats, 'host') 
            + makeRow30(stats, 'guest') +
        '</table>\
    </div>';
        

        var replaces = {
            '%ID%' : stats['id'],
            '%LEAGUE%' : stats['league'], // league
            '%START_TIME%' : startTime2s(stats['startTime']),
            '%TIME%' : stats['time'],
            '%hostRank%' : stats['host']['rank'],
            '%hostName%' : stats['host']['name'],
            '%guestRank%' : stats['guest']['rank'],
            '%guestName%' : stats['guest']['name'],
            '%GOALS_MATCH%' : makeGoalString(stats, 'match'),
            '%7%' : makeShotStatString(stats, "match", "host"),
            '%8%' : makeShotStatString(stats, "match", "guest"),
            '%9%' : makeStatString(stats, "match", "bp"),
            '%10%' : makeShotStatString(stats, "half1", "host"),
            '%11%' : makeShotStatString(stats, "half1", "guest"),
            '%12%' : makeStatString(stats, "half1", "bp"),
            '%13%' : makeShotStatString(stats, "half2", "host"),
            '%14%' : makeShotStatString(stats, "half2", "guest"),
            '%15%' : makeStatString(stats, "half2", "bp"),
            '%16%' : makeShotStatString(stats, "last15", "host"),
            '%17%' : makeShotStatString(stats, "last15", "guest"),
            '%18%' : makeStatString(stats, "last15", "bp"),
            '%last%' : globalLastMinutes,
            "%19%" : stats['time'] - 29,
            "%20%" : stats['time'] - 14,
            "%21%" : stats['time']
        }

        Object.keys(replaces).forEach(function(key){
            var rexp = new RegExp(key, 'g')
            view = view.replace(rexp, replaces[key]);
        });

        return view;

    }
}

