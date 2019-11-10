function Match(game) {
    const updateMatchInterval = 60. // seconds
    var update = false;
    var startTime = game['time'];
    var timeCorrection = 0;
    var matchId = game['id'];
    var league = game['league'] ? game['league'] : '';
    var handicap = null;
    var updateCount = 0;
    var onUpdate;
    var updateInProgress = false;;
    var allowWatching = false;
    var updateInterval;
    var lastNotified = null;

    var isFavorite = false;

    var host = new Team(game['host']);
    var guest = new Team(game['guest']);

    this.getMatchId = function() {
        return matchId;
    }

    this.isUpdated = function() {
        return updateCount >= 3;
    }
    
    this.setWatch = function(allowWatch) {
        allowWatching = allowWatch;
        clearInterval(updateInterval);
        if (allowWatching) {
            updateInterval = setInterval(updateStatus, updateMatchInterval * 1000.);
            updateStatus();
        }
    }

    this.setStartTime = function (newTime) {
        startTime = newTime;
    }

    this.getStartTime = function (newTime) {
        return startTime;
    }

    this.setTimeCorrection = function (correction) {
        timeCorrection += correction;
    }

    this.getTimeCorrection = function (correction) {
        return timeCorrection;
    }

    function getRealStartTime() {
        return startTime + timeCorrection;
    }

    this.getRealStartTime = function () {
        return getRealStartTime();
    }

    function getMatchTime(time) {
        var mins = time - getRealStartTime();
        if (mins > BREAK_START && mins <= BREAK_END) {
            mins = 45;
        } else if (mins > BREAK_END) {
            mins -= BREAK_END - BREAK_START;
        }
        return mins;        
    }
    
    this.getMatchTime = function (time) {
        return getMatchTime(time);
    }

    this.isWatching = function() {
        return allowWatching;
    }

    this.makeMatchStats = function (time) {
        var matchTime = getMatchTime(time);
        return {
            'id' : matchId,
            'league' : league,
            'startTime' : startTime,
            'time' : matchTime,
            'host' : host.makeTeamStats(time, getRealStartTime(), getMatchTime(time)),
            'guest': guest.makeTeamStats(time, getRealStartTime(), getMatchTime(time)),
            'isHalf1' : matchTime > 0 && matchTime < BREAK_START,
            'isBreak' : matchTime == BREAK_START,
            'isHalf2' : matchTime > BREAK_END && matchTime <= MAX_WATCHING_TIME
        }
    }

    function onGetStatistics(time, stats) {
        if (!stats) {
            return;
        }
        updateCount++;
        host.addStats(time, stats['host']);
        guest.addStats(time, stats['guest']);
        handicap = stats['handicap'];
        if (onUpdate) {
            onUpdate();
        }
    }

    function updateStatus() {
        if (updateInProgress || !allowGlobalUpdate() || !allowWatching) {
            return;
        }
        var time = getTimestamp();
        updateInProgress = true;
        getStatistics(matchId, function(stats) {
            if (stats) {
                onGetStatistics(time, stats);
            }
            updateInProgress = false;
        });
    }

    this.setCallback = function(callback) {
        onUpdate = callback;
    }
    
    this.isBreak = function(time) {
        var t = getMatchTime(time); 
        return  t == 45;
    }
    this.toggleFavorite = function() {
        isFavorite = !isFavorite;
        return this.isFavorite();
    }
    this.isFavorite = function() {
        return isFavorite;
    }
    this.setNotified = function(time) {
        lastNotified = time;
    }
    this.isNotified = function(time) {
        return lastNotified == time;
    }
    this.toString = function() {
        var data = {
            host  : host.toString(),
            guest : guest.toString()
        };
        return JSON.stringify(data);
    }
    this.fromString = function(stringified) {
        var data;
        try {
            data = JSON.parse(stringified);
            host.fromString(data['host']);
            guest.fromString(data['guest']);
        } catch (e) {}
    }
}
