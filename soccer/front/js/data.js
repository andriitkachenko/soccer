const not_allowed_leagues = [
    "fut ifi",
    "jap futl",
    'rus fdh',
    'spa fdn',
    'estonia wt',
    'afc fc',
    'bswcp',
    'labsc'
    
];

const not_allowed_team_names = [
    "futsal",
    "indoor"
];

function getFullGameList() {
    var arr = JSON.parse(GAMES);
    if (Array.isArray(arr)) {
        return arr;
    }
    return [];
}


function makeTeam(name, rank) {
    var team = {};
    if (teamNameOk(name)) {
        team['name'] = name;
        if (rank)
            team['rank'] = rank;
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

function getGameList(dateKey, callback) {
    var fullList = getFullGameList();
    if (fullList.length) {
        callback(makeGameList(fullList));
    }
}

function makeGameList(arr) {
/*
game: id, time, league, host/guest : name, rank
*/    
    var list = [];
    if (!Array.isArray(arr)) {
        return list;
    }
    arr.forEach(function(data){
        if (data['id'] && data['start_time'] && data['host'] && data['guest']) {
            if (!leagueOK(data['league'])) {
                return;
            }
            var game = {};
            game['id'] = data['id'];
            game['time'] = time2minutes(data['start_time']);
            game['league'] = data['league'];
            var time = data['game_time'].replace('+', '').trim();
            var started =  time != '';
            var halfTime =  time == 'HT';
            var overtime = time == 'Ot';
            var running = !isNaN(time) || halfTime || overtime;
            if (started && !running) {
                return;
            }
//ENG U23 D113:3081[18] Huddersfield U232 - 1Hull City U23 [10]1 - 1                    
//TUR U1913:0090+ Bucaspor U192 - 2Altay Spor KulubuU19 1 - 2
//ROMC17:50HT FC Sacele0 - 3ACS Olimpic Cetate Rasnov [ROM D3-40]0 - 3
//UEFA EL18:59 [GEO D1-3] FC Saburtalo Tbilisi(N)-FC Avan Academy [ARM D1-4]
//SLOC17:00Ot[SLO D3-22] Bistrica2 - 2Brda [SLO D2-15]1 - 2
//CZEC18:0045+ Olesnice U Bouzova1 - 1Otrokovice [CZE CFLM-6]
            var host = data['host'];
            var matches = host.match(/^(\[[^\]]+\])/);
            var hostRank = matches ? matches[0] : "";
            if (hostRank.length) {
                host = host.substr(hostRank.length).trim();
            }
            var guest = data['guest'];
            matches = guest.match(/(\[[^\]]+\])$/);
            var guestRank = matches ? matches[0] : "";
            if (guestRank.length) {
                guest = data['guest'].substr(0, guest.length - guestRank.length).trim();
            }
            game['host'] = makeTeam(host, hostRank);
            game['guest'] = makeTeam(guest, guestRank);
            if (game['host'] && game['guest']) {
                list.push(game);
            } else {
                if (console && console.log) console.log(data[key]);
            }
        }
    });
    return list;
}
    
