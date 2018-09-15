const not_allowed_leagues = [
    "fut ifi",
    "jap futl",
    'rus fdh',
    'spa fdn',
    'estonia wt',
    'afc fc'
];

const not_allowed_team_names = [
    "futsal",
    "indoor"
];

function getFullGameList() {
    return GAMES;
}


function parseTeam(str) {
    var team = {};
    var s = str.trim();
    var idx1 = s.indexOf('[');
    var idx2 = s.indexOf(']');
    if (idx1 != -1 && idx2 != -1) {
        team['rank'] = s.substr(idx1, idx2 - idx1 + 1);
        team['name'] = idx1 == 0 ? s.substr(idx2 + 1) : s.substr(0, idx1);
    } else {
        team['name'] = s;
    }
    if (!teamNameOk(team['name'])) {
        team = {};
    }
    return team;
}

function teamNameOk(name) {
    var res = true;
    not_allowed_team_names.forEach(function(l) {
        res = res && (name.indexOf(l) == -1)
    });
    return res;
}

function leagueOK(league) {
    var res = true;
    not_allowed_leagues.forEach(function(l) {
        res = res && !(league.toLowerCase() == l)
    });
    return res;
}

function getGameListFromFile(dateKey, callback) {
    var reader = new FileReader();
    var dataFile = "data/" + dateKey + ".json";
    reader.onload = function(e) {
        var arr = JSON.parse(reader.result);
        if (Array.isArray(arr)) {
            callback(makeGameList(arr));
        }

    }
    reader.readAsText(dataFile);
}

function getGameList(dateKey, callback) {
    var fullList = getFullGameList();
    if (dateKey in fullList) {
        var json = fullList[dateKey];
        var arr = JSON.parse(json);
        if (Array.isArray(arr)) {
            callback(makeGameList(arr));
        }
    }
}

function makeGameList(arr) {
/*
game: id, time, league, host/guest : name, rank
*/    
    var list = [];
    var key = "data";

    if (!Array.isArray(arr)) {
        return list;
    }

    arr.forEach(function(data){
        if (data[key] && data[key + '-id']) {
            var str = data[key];
            var id = data[key + '-id'];
            var game = {};
            game['id'] = id.replace('tr1_', '');
            var idx = str.indexOf(":");
            game['time'] = time2minutes(str.substr(idx - 2, 5));
            game['league'] = str.substr(0, idx - 2);
            if (!leagueOK(game['league'])) {
                return;
            }
            idx += 3;
            started = str[idx] != ' ';
            running = !isNaN(str[idx]) || str.substr(idx, 2) == 'HT';
            if (started && !running) {
                return;
            }
//ENG U23 D113:3081[18] Huddersfield U232 - 1Hull City U23 [10]1 - 1                    
//  TUR U1913:0090+ Bucaspor U192 - 2Altay Spor KulubuU19 1 - 2
            if (started) {
                idx += 2;
                if (str[idx + 3] == "+") {
                    idx += 1;
                }
            }
            var idx2 = str.indexOf("]", idx);
            var idx3 = str.indexOf("-", idx2 == -1 ? idx : idx2);
            var host =  str.substr(idx, idx3 - idx);
            var guest =  str.substr(idx3 + 1);
            game['host'] = parseTeam(host);
            game['guest'] = parseTeam(guest);
            if (game['host'] && game['guest']) {
                list.push(game);
            }
        }
    });
    return list;
}

