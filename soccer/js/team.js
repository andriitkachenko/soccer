function Team(team) {
    var name = (team && team['name']) ? team['name'] : '';
    var rank = (team && team['rank']) ? team['rank'] : '';;
    var stats = [];
    const zeroStats = {
        "gl" : 0,
        "sg" : 0,
        "sh" : 0,
        "bp" : 50,
        "rc" : 0
    }

    this.addStats = function(time, data) {
        if (data && data['name']) {
            if (name == '') {
                name = data['name'];
            }
            delete data['name'];
        }
        data['time'] = time;
        var idx = stats.findIndex(function(s){
            s['time'] == time;
        });
        if (idx != -1 && data['host'] && data['guest']) {
            stats.splice(idx, 1);
        }
        stats.push(data);
    }

    this.addUnchangedStats = function(time) {
        if (stats.length == 0) {
            return;
        }
        var stat = jQuery.extend(true, {}, stats[stats.length - 1]);
        this.addStats(time, stat);
    }

    function getLast15(time, startTime) {
        var i15 = -1;
        for (i = 0; i < stats.length - 1 && i15 == -1; i++) {
            if (time - stats[i]['time'] <= globalLastMinutes) {
                i15 = i;
            }
        }
        if (i15 == -1) {
            return zeroStats;
        }
        return  subtractStats(stats[stats.length - 1], stats[i15]);
    }

    function getHalf1 (startTime) {
        var half1 = {};
        for (i = 0; i < stats.length; i++) {
            if (stats[i]['time'] - startTime <= 55) {
                half1 = stats[i];
            }
        }
        return half1;
    }

    function getHalf2 (startTime) {
        var half2 = {};
        if (stats.length == 0) {
            return half2;
        }
        var last = stats[stats.length - 1];
        if (last['time'] - startTime < 60.) {
            return half2;
        }  
        var half1 = getHalf1(startTime);
        if (jQuery.isEmptyObject(half1)) {
            half1 = stats[0];
        }
        return  subtractStats(last, half1);
    }

    function getEvent(stat, current) {
        /*
         1 - no event
        2 - red card
        3 - shot
        4 - shot on goal
        5 - goal
        */ 
        var s = subtractStats(stat, current);
        var event = 1;
        if (s['rd']) event = 2;
        if (s['sh']) event = 3;
        if (s['sg']) event = 4;
        if (s['gl']) event = 5;
        return event;
    }

    function getCurrent () {
        return  stats.length > 0 ? stats[stats.length - 1] : {};
    }

    function getEvents30 (time, startTime) {
        var period = 30;
        var s30 = [];
        for (var i = 1; i <= period; i++) {
            var eventTime = time - period + i;
            var event = -1;
            if (eventTime >= startTime) {
                event = 0;
                var idx = stats.findIndex(function(s) {
                    return s['time'] == eventTime;
                });
                if (idx != -1) {
                    var  prev = idx ? stats[idx - 1] : zeroStats;
                    event = getEvent(stats[idx], prev);
                }
            }
            s30.push({'time' : eventTime, 'event' : event});
        } 
        return  s30;
    }
   
    this.makeTeamStats = function(time, startTime) {
        var result = {};
    
        result['name'] = name;
        result['rank'] = rank;
    
        if (stats.length > 0) {
            result['last15'] = getLast15(time, startTime);
            result['half1'] = getHalf1(startTime);
            result['half2'] = getHalf2(startTime);
            result['match'] = getCurrent();
            result['events30'] = getEvents30(time, startTime);
        }
        return result;
    }

    
}