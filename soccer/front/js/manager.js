function MatchManager() {
    var matches = [];
    const updateViewInterval = 30.; // seconds
    var updateInterval;
    var showInterestingOnly = false;
    var currentList = [];
    var sortFunction = sortByRealStartTime;
    var isUpdating = false;

    this.setMatches = function(list) {
        list.forEach(function(game) {
            if (!matchExists(game['id'])) {
                addMatch(game);
            }
        });
        updateList();
    }

    this.toggleUpdate = function(blockUpdate) {
        if (blockUpdate) {
            clearInterval(updateInterval);
        } else {
            updateList();
            setTimeout(updateList, 3000);
            updateInterval = setInterval(updateList, updateViewInterval * 1000);
        }
    }

    function addMatch(game) {
        var m = new Match(game);
        matches.push(m);
    }

    function findMatchIndex(id) {
        var idx = -1;
        for(var idx = -1, i = 0; idx == -1 && i < matches.length; i++) {
            if(matches[i].getMatchId() == id)
                idx = i;
        }
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
            removeMatchView(id);
        }
    }

    this.updateList = function() {
        updateList();
    }

    this.toggleFavorite = function(id) {
        var m = this.getMatch(id);
        if (m) {
            var favStatus = m.toggleFavorite();
            var el = currentMatchList.find('div.match#' + id + ' > div.info td.star > div');
            if (favStatus) el.addClass('active')
            else el.removeClass('active');
        }
    }

//
//
//
    function updateList () {
        if (isUpdating || currentList.length && !allowGlobalUpdate()) {
            return;
        }
        isUpdating = true;
        var list = [];
        var time = getTimestamp();
        for (var i = 0; i < matches.length; i++) {
            var m = matches[i];
            var mtime = m.getMatchTime(time);
            var maxTime = m.isWatching() ? MAX_WATCHING_TIME : MAX_START_WATCHING_TIME;
            if (mtime >= MIN_WATCHING_TIME && mtime <= maxTime) {
                if (!m.isWatching()) {
                    m.setWatch(allowGlobalUpdate());
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
            return sortMatchList(time, a, b);
        });
        updateView();
        isUpdating = false;
    }

    function removeMatches(ids) {
        if (!ids.length) {
            return;
        }
        var cleared = [];
        matches.forEach(function(m) {
            var id = m.getMatchId();
            if (!ids.includes(id)) {
                cleared.push(m);
            }
        });
        matches = cleared;
    }

    this.clearList = function() {
        var time = getTimestamp();
        var toRemove = [];
        var times = currentList.map(function(m) {
            return m.getMatchTime(time);
        });
        var maxTime = Math.max.apply(null, times);
        matches.forEach(function(m) {
            if (m.getMatchTime(time) == maxTime && !m.isFavorite()) {
                toRemove.push(m.getMatchId());
            }
        });
        removeMatches(toRemove);
        updateList();
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

    function getHalfShotsOnGoal(time, match) {
        var half = 'half1';
        if (match.getMatchTime(time) > 45) {
            half = 'half2';
        }
        return getMaxStatType(time, match, half, "sg");
    }

    function getLastShots(time, match) {
        return getMaxStatType(time, match, 'last15', "sh");
    }

    function sortByMatchShots(time, a, b) {
        return getMatchShots(time, b) - getMatchShots(time, a);
    }
    function sortByFavorite(a, b) {
        return (+b.isFavorite()) - (+a.isFavorite());
    }
    function sortByNotified(a, b, time) {
        return (+b.isNotified(time)) - (+a.isNotified(time));
    }
    function sortByBreakTime(time, a, b) {
        return (+a.isBreak(time)) - (+b.isBreak(time));
    }
    function sortByHalfShots(time, a, b) {
        var s = sortByBreakTime(time, a, b);
        if (s != 0) return s;
        s = getHalfShots(time, b) - getHalfShots(time, a);
        if (s != 0) return s;
        s = getHalfShotsOnGoal(time, b) - getHalfShotsOnGoal(time, a);
        return s;
    }
    function sortByLastShots(time, a, b) {
        return getLastShots(time, b) - getLastShots(time, a);
    }
    function sortByRealStartTime(time, a, b) {
        var s = a.getRealStartTime() - b.getRealStartTime();
        return s;
    }
    this.setSortByHalfShots = function() {
        sortFunction = sortByHalfShots;
    }
    this.setSortByMatchShots = function() {
        sortFunction = sortByMatchShots;
    }
    this.setSortByMatchTime = function() {
        sortFunction = sortByRealStartTime;
    }
    this.setSortByLastShots = function() {
        sortFunction = sortByLastShots;
    }
    function sortMatchList(time, a, b) {
        var s = sortByFavorite(a, b);
        if (s != 0) return s;
        s = sortByNotified(a, b, time);
        if (s != 0) return s;
        s = sortFunction(time, a, b);
        if (s != 0) return s;
        return a.getMatchId() - b.getMatchId();
    }

    function getNotificationData(stats) {
        if (!stats['time'] || !isStatOk(stats) || !stats['host']['match'] || ! stats['guest']['match']) {
            return false;
        }
        var half1 = stats['time'] < 46;
        var half2 = !half1;
        var halfTime = stats['time'] == 45;

        var startTime = (half1 && stats['time'] < 30)  || (half2 && stats['time'] < 75);
        var endTime   = (half1 && stats['time'] >= 45) || (half2 && stats['time'] > 90);

        var isDraw = stats['host']['match']['gl'] == stats['guest']['match']['gl'];
        var isHostLeading = stats['host']['match']['gl'] > stats['guest']['match']['gl'];
        var isGuestLeading = !isDraw && !isHostLeading;

        var half = 'half' + (half1 ? '1' : '2');

        half1Shots = half1
            && stats['time'] >= 30
            && stats['time'] <= 40
            && stats['time'] / extractMaxStatType(stats, 'half1', 'sh') <= 4.5
            && extractMaxStatType(stats, 'last15', 'sh') >= 3;
        half2Shots = half2
            && stats['time'] >= 75
            && stats['time'] <  90
            && (stats['time'] - 45) / extractMaxStatType(stats, 'half2', 'sh') <= 4.5
            && extractMaxStatType(stats, 'last15', 'sh') >= 3;
        domination = extractMinStatType(stats, half, 'sh') <= 3
            && (extractMaxStatType(stats, half, 'sh') - extractMinStatType(stats, half, 'sh')) > 6;
        shotsOnGoal =  extractMaxStatType(stats, half, 'sg') >= 5;

        var ok = true
            && !halfTime
            && !startTime
            && !endTime
            && (
                half1Shots
                || half2Shots
                || domination
                || shotsOnGoal
            );

        if (!ok) {
            return null;
        }
        return {
            time : stats['time'],
            host : stats['host']['name'],
            guest : stats['guest']['name'],
            scores : makeGoalString(stats, 'match'),
            hostShots : makeShotStatString(stats, half, "host"),
            guestShots : makeShotStatString(stats, half, "guest")
        }
    }

    function getDefaultStat(type) {
        return type == 'bp' ? 50 : 0;
    }
    function extractTeamCurrentStat(stats, team, type) {
        var ok = stats.hasOwnProperty('isHalf1')
              && stats.hasOwnProperty('isHalf2')
              && stats.hasOwnProperty('isBreak');
        if (!ok || stats['isBreak']) {
            return getDefaultStat(type);
        }
        var part = false;
        if (stats['isHalf1']) part = 'half1';
        if (stats['isHalf2']) part = 'half2';
        if (!part) {
            return getDefaultStat(type);
        }
        return extractTeamStat(stats, team, part, type);
    }

    function extractTeamStat(stats, team, part, type) {
        var ok = stats && stats[team] && stats[team][part] && stats[team][part].hasOwnProperty(type);
        return ok ? stats[team][part][type] : getDefaultStat(type);
    }

    function isTeamDominating(stats, team) {
        var hostShots = extractTeamCurrentStat(stats, 'host', 'sh');
        var guestShots = extractTeamCurrentStat(stats, 'guest', 'sh');
        var diff = 0;
        if (team == 'host') diff = hostShots - guestShots;
        if (team == 'guest') diff = guestShots - hostShots;
        return diff >= 5;
    }

    function isTeamWinning(stats, team) {
        var hostGoals = extractTeamStat(stats, 'host', 'match', 'gl');
        var guestGoals = extractTeamStat(stats, 'guest', 'match', 'gl');
        return (team == 'host' && hostGoals > guestGoals)
            || (team == 'guest' && hostGoals < guestGoals);
    }

    function isInteresting(stats) {
        var ok = stats.hasOwnProperty('isHalf1') && stats.hasOwnProperty('isHalf2');
        return ok
        && (
               stats['isHalf1'] && extractMaxStatType(stats, 'half1', 'sh') >= 6
            || stats['isHalf2'] && extractMaxStatType(stats, 'half2', 'sh') >= 6
            || isTeamDominating(stats, 'host') && !isTeamWinning(stats, 'host')
            || isTeamDominating(stats, 'guest') && !isTeamWinning(stats, 'guest')
        );
    }

///
/// view
///

    function updateView() {
        if (!currentMatchList)
            return;
        var notificationData = [];
        var time = getTimestamp();
        var frag = $(document.createDocumentFragment());
        var toRemove = [];
        var count = 0;
        currentList.forEach(function(m) {
            var s = m.toString();
            var id = m.getMatchId();
            var stats = m.makeMatchStats(time);
            // remove empty
            if (m.isUpdated() && stats['time'] > MIN_CLEAR_TIME) {
                var s = stats['host']['match'];
                // TODO check in team
                var emptyStat = !s || (!s.hasOwnProperty('sh') && !s.hasOwnProperty('bp'));
                if (emptyStat) {
                    toRemove.push(m.getMatchId);
                    return;
                }
            }
            var notifData = getNotificationData(stats);
            // filter out non interesting
            if (!notifData && showInterestingOnly && !isInteresting(stats)) {
                return;
            }
            var view = $(makeMatchView(stats));
            if (m.isFavorite()) {
                view.find('td.star > div').addClass('active');
            }
            view.find('td.star').click(function(e){
                manager.toggleFavorite(id);
            });
            var timeCell = view.find('td.time');
            timeCell.click(function(e){
                setTimePanel(id);
            });
            if (notifData) {
                timeCell.addClass('notify');
                if (!m.isNotified(time)) {
                    m.setNotified(time);
                    notificationData.push(notifData);
                }
            }
            frag.append(view);
            count++;
        });
        currentMatchList.children().remove();
        currentMatchList.append(frag);
        $('div#footer > div#list_info').html( count + ' games. ' + GAME_LIST_TIME);
        setTimeout(function() {
            removeMatches(toRemove);
            showNotification(makeNotification(notificationData));
        }, 250);
    }

    function removeMatchView(id) {
        $("div.match#" + id).remove();
    }
    function makeNotification(notifData) {
        if (!notifData.length)
            return "";
        var texts = notifData.map(function(n) {
            var template = "%TIME% . %SCORE% . %HOST_SHOTS% / %GUEST_SHOTS% . %HOST% - %GUEST%";
            var replaces = {
                "%TIME%" : n.time,
                "%HOST%" : n.host.substring(0, 6),
                '%SCORE%' : n.scores,
                "%HOST_SHOTS%" : n.hostShots,
                "%GUEST%" : n.guest.substring(0, 6),
                "%GUEST_SHOTS%" : n.guestShots
            }
            return strReplaces(template, replaces);
        });
        return texts.join("\n");
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
        var rows = [];
        var e30 = stats[team]['events30'];
        for (var i = 0; i < 30; i++) {
            var text = '';
            var color = 'white';
            var event = (e30 && e30[i] && e30[i]['event']) ? e30[i]['event'] : 0;
            switch(event) {
                //fail
                case 0 : text = '-'; break;
                //no change
                case 1 : text = '.'; break;
                // red card
                case 2 : color = 'red'; break;
                // shot
                case 3 : color = 'orange'; break;
                // shot on goal
                case 4 : color = 'green'; break;
                // goal
                case 5 : color = 'blue'; break;
            }
            var td = '<td class="event">\
                <div class="event" style="background-color:' + color + '">' + text + '</div></td>';
            rows.push(td);
        }
        return '<tr>' + rows.join("\n") + '</tr>';
    }

    function makeMatchView(stats) {
        var view =
            '<div class="match" id="%ID%" style="text-align:center">\
                <div class="info">\
                    <table class="info">\
                        <tr>\
                            <td class="close" rowspan="2"><button class="close" type="button" onclick="removeMatch(%ID%)">X</button></td>\
                            <td class="league"><div class="ellipsis">%LEAGUE%</div></td>\
                            <td class="time" rowspan="2">%TIME%</td>\
                            <td class="team">\
                                <div class="ellipsis">\
                                    <span class="name">%hostName%</span>&nbsp;<span class="rank">%hostRank%</span>\
                                </div></td>\
                            <td class="scores" rowspan="2">%GOALS_MATCH%</td> \
                            <td class="star" rowspan="2"><div></div></td> \
                        </tr>\
                        <tr>\
                            <td class="start_time">%START_TIME%</td> \
                            <td class="team">\
                                <div class="ellipsis">\
                                    <span class="name">%guestName%</span>&nbsp;<span class="rank">%guestRank%</span>\
                                </div></td>\
                        </tr>\
                    </table>\
                </div>\
                <div class="stat">\
                    <table class="stat" cellspacing="0"\
                        <tr>\
                            <td class="match_part">M</td>\
                            <td class="shots host match" rowspan="2">%SHOTS_HOST_MATCH%</td>\
                            <td class="shots guest match" rowspan="2">%SHOTS_GUEST_MATCH%</td>\
                            <td class="match_part">H1</td>\
                            <td class="shots host half1" rowspan="2">%SHOTS_HOST_HALF1%</td>\
                            <td class="shots guest half1" rowspan="2">%SHOTS_GUEST_HALF1%</td>\
                            <td class="match_part">H2</td>\
                            <td class="shots host half2" rowspan="2">%SHOTS_HOST_HALF2%</td>\
                            <td class="shots guest half2" rowspan="2">%SHOTS_GUEST_HALF2%</td>\
                            <td class="match_part">L%last%</td>\
                            <td class="shots host recent" rowspan="2">%SHOTS_HOST_RECENT%</td>\
                            <td class="shots guest recent" rowspan="2">%SHOTS_GUEST_RECENT%</td>\
                        </tr>\
                        <tr>\
                            <td class="ball_posession match">%BALL_POSESSION_MATCH%</td>\
                            <td class="ball_posession half1">%BALL_POSESSION_HALF1%</td>\
                            <td class="ball_posession half2">%BALL_POSESSION_HALF2%</td>\
                            <td class="ball_posession recent">%BALL_POSESSION_RECENT%</td>\
                        </tr>\
                    </table>\
                </div>\
                <div class="events">\
                    <table class="events" cellspacing="0">' +
                       makeRow30(stats, 'host') +
                       makeRow30(stats, 'guest') +
                    '</table>\
                </div>\
            </div>';
        var replaces = {
            '%ID%' : stats['id'],
            '%LEAGUE%' : stats['league'], // league
            '%START_TIME%' : startTime2s(stats['startTime']),
            '%TIME%' : stats['time'] == 45 ? 'HT' : stats['time'],
            '%hostRank%' : stats['host']['rank'],
            '%hostName%' : stats['host']['name'],
            '%guestRank%' : stats['guest']['rank'],
            '%guestName%' : stats['guest']['name'],
            '%GOALS_MATCH%' : makeGoalString(stats, 'match'),
            '%SHOTS_HOST_MATCH%' : makeShotStatString(stats, "match", "host"),
            '%SHOTS_GUEST_MATCH%' : makeShotStatString(stats, "match", "guest"),
            '%BALL_POSESSION_MATCH%' : makeStatString(stats, "match", "bp"),
            '%SHOTS_HOST_HALF1%' : makeShotStatString(stats, "half1", "host"),
            '%SHOTS_GUEST_HALF1%' : makeShotStatString(stats, "half1", "guest"),
            '%BALL_POSESSION_HALF1%' : makeStatString(stats, "half1", "bp"),
            '%SHOTS_HOST_HALF2%' : makeShotStatString(stats, "half2", "host"),
            '%SHOTS_GUEST_HALF2%' : makeShotStatString(stats, "half2", "guest"),
            '%BALL_POSESSION_HALF2%' : makeStatString(stats, "half2", "bp"),
            '%SHOTS_HOST_RECENT%' : makeShotStatString(stats, "last15", "host"),
            '%SHOTS_GUEST_RECENT%' : makeShotStatString(stats, "last15", "guest"),
            '%BALL_POSESSION_RECENT%' : makeStatString(stats, "last15", "bp"),
            '%last%' : globalLastMinutes
        }
        return strReplaces(view, replaces);
    }

    function strReplaces (str, replaces) {
        Object.keys(replaces).forEach(function(key){
            var rexp = new RegExp(key, 'g')
            str = str.replace(rexp, replaces[key]);
        });
        return str;
    }

    this.toggleMatchFilter = function() {
        showInterestingOnly = !showInterestingOnly;
    }
    this.getMatchFilter = function() {
        return showInterestingOnly;
    }
    this.saveToStorage = function() {
        currentList.forEach(function(m) {
            window.localStorage.setItem('' + m.getMatchId(), m.toString());
        });
    }
    this.restoreFromStorage = function() {
        matches.forEach(function(m) {
            var cached = window.localStorage.getItem('' + m.getMatchId());
            if (cached) {
                m.fromString(cached);
            }
        });
        window.localStorage.clear();
    }
}
