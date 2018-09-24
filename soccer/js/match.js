function Match(game) {
    const updateMatchInterval = 60. // seconds
    var update = false;
    var startTime = game['time'];
    var timeCorrection = 0;
    var matchId = game['id'];
    var league = game['league'] ? game['league'] : '';
    var lastResponse = '';
    var onUpdate;
    var updateInProgress = false;;
    var allowWatching = false;
    var updateInterval;

    var host = new Team(game['host']);
    var guest = new Team(game['guest']);


    this.getMatchId = function() {
        return matchId;
    }
    
    this.setWatch = function(allowWatch) {
        allowWatching = allowWatch;
        if (allowWatching) {
            updateInterval = setInterval(updateStatus, updateMatchInterval * 1000.);
            updateStatus();
        } else if (updateInterval) {
            clearInterval(updateInterval);
            updateInterval = undefined;
        }
    }

    this.setStartTime = function (newTime) {
        startTime = newTime;
    }

    this.getStartTime = function (newTime) {
        return startTime;
    }

    this.setTimeCorrection = function (correction) {
        timeCorrection = correction;
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
        if (mins > 45 && mins <= 60) {
            mins = 45;
        } else if (mins > 60) {
            mins -= 15;
        }
        return mins;        
    }
    
    this.getMatchTime = function (time) {
        return getMatchTime(time);
    }

    this.isWatching = function() {
        return allowWatching;
    }

    function updateTeams(time, data) {
        host.addStats(time, data ? data['host'] : data);
        guest.addStats(time, data ? data['guest'] : data);
    }

    function setUnchangedStatus(time) {
        host.addUnchangedStats(time);
        guest.addUnchangedStats(time);
    }

    this.makeMatchStats = function (time) {
        var matchTime = getMatchTime(time);
        return {
            'id' : matchId,
            'league' : league,
            'startTime' : startTime,
            'time' : matchTime,
            'host' : host.makeTeamStats(time, getRealStartTime(), getMatchTime(time)),
            'guest' : guest.makeTeamStats(time, getRealStartTime(), getMatchTime(time))
        }
    }

    function onGetStatistics(time, response) {
        if (response == "") {
            return;
        }
        if (response == lastResponse) {
            setUnchangedStatus(time);
        } else {
            var data = parseResponse(response);
            updateTeams(time, data);
            if (onUpdate) {
                onUpdate();
            }
            lastResponse = response;
        }
    }

    function updateStatus() {
        if (updateInProgress || !allowGlobalUpdate() || !allowWatching) {
            return;
        }
        var time = getTimestamp();
        updateInProgress = true;
        getStatistics(matchId, function(response) {
            onGetStatistics(time, response);
            updateInProgress = false;
        });
    }

    this.updateStatus = function () {
        updateStatus();
    }

    this.setCallback = function(callback) {
        onUpdate = callback;
    }

    this.isBreak = function(time) {
        return getMatchTime(time) >= BREAK_START && getMatchTime <= BREAK_END;
    }
 }
