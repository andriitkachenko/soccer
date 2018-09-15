var maxStatRequests = 5;
var statRequestInterval = 3; // seconds
var runningRequest = false;
var dataUrl = "http://data.unogoal.life/detail.aspx?ID=";
var baseUrl = "php/utils.php?url=";


var statMap = {
    "Shots": "sh",
    "Shots on Goal": "sg",
    "Ball Possession": "bp",
    "Red Cards": "rc"
}

var statKeys = makeStatKeys();

function makeStatKeys() {
    var keys = [];
    for (var key in statMap) {
        if (statMap.hasOwnProperty(key)) {
            keys.push(key);
        }
    }
    return keys;
}

// load statistics

function getStatistics (matchId, onResponse) {
    makeRequestSeries(1, matchId, onResponse)
}

function makeRequestSeries(n, id, onResponse){
    if (n > maxStatRequests) {
        onResponse("");
    } else {
        makeRequest(id, function(text){
            if (text == "") {
                setTimeout(function() {makeRequestSeries(n + 1, id, onResponse);}, statRequestInterval * 1000);
            } else {
                onResponse(text);
            }
        });
    }
}

function makeRequest(id, onResponse) {
    var statUrl = dataUrl + id;
    var url = server ? (baseUrl + encodeURIComponent(statUrl)) : statUrl;
    var runningRequest = true;
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            var text = "";
            if (server) {
                text = response;
            } else if (response.responseText) {
                text = response.responseText;
            }
            onResponse(text);
            runningRequest = false;
        },
        error : function () {
            onResponse("");
            runningRequest = false;
        }
    });
}

// parse response

function parseResponse(responseText) {
    var host = {};
    var guest = {};
    var data = responseText.replace(/<\/?result>/g, '');
    var html = $('<textarea />').html(data).text();
    html = html.replace(/\r?\n *|\r */g, '').replace(/<\/?b>|<\/?td>/g, '');
    var dom = $.parseHTML(html);
    var domCount = server ? 5 : 6; 
    var nStat = domCount - 1;
    var nMeta = domCount - 2;
    if (dom.length >= domCount) {
        var stats = dom[nStat].rows;
        for (var i = 0; i< stats.length; i++) {
            var row = stats[i];
            statKeys.forEach(function(key, i, statKeys){
                if (addStatPiece(row, host, guest, key, statMap[key])) return;
            });
        }
        try {
            var hn = dom[nMeta].childNodes[0].childNodes[0].childNodes[0].childNodes[0].childNodes[0].childNodes[0].innerText;
            var gn = dom[nMeta].childNodes[0].childNodes[0].childNodes[2].childNodes[0].childNodes[0].childNodes[0].innerText;
            var handicap = dom[nMeta].childNodes[0].childNodes[0].childNodes[1].childNodes[0].childNodes[0].childNodes[0].innerText;
            var goals = dom[nMeta].childNodes[0].childNodes[0].childNodes[1].childNodes[0].childNodes[0].childNodes[1].innerText;
        } catch (e) {
           return getTeamNames(html, host, guest);
        }
        host['name'] = hn;
        guest['name'] = gn;
        goals = goals.trim().toLowerCase().split("min");
        var hg;
        var gg;
        if (goals.length == 2) {
            hg = goals[0];
            gg = goals[1];
        }
        if (hg && !isNaN(hg) && gg && !isNaN(gg)) {
            host['gl'] = parseInt(hg);
            guest['gl'] = parseInt(gg);
        }
    }
    return {"host" : host, "guest" : guest};
}

function subtractStats(stats2, stats1) { // stats2 - stats1
    var result = {};
    var keys1 = Object.keys(stats1);
    var keys2 = Object.keys(stats2);
    keys2.forEach(function(key){
        if (keys1.includes(key)) {
            result[key] = stats2[key] - stats1[key];
        } else {
            result[key] = stats2[key];
        }
    });
    return result;
}

function getStatPiece(row, name) {
    var result = {};
    var text = row.innerText;
    var idx = text.indexOf(name);
    if (idx == -1) {
        return result;
    }
    var h = text.substr(0, idx).trim().replace('%', '');
    var g = text.substr(idx + name.length).trim().replace('%', '');
    if (!isNaN(h) && !isNaN(g)) {
        result['host'] = parseInt(h);
        result['guest'] = parseInt(g);
    }
    return result;
}

function addStatPiece(row, host, guest, piece, key) {
    var result = false;
    var stat = getStatPiece(row, piece);
    if (Object.keys(stat).length == 2) {
        host[key] = stat['host'];
        guest[key] = stat['guest'];
        result = true;
    }
    return result;
}

function getTeamNames(html, host, guest) {
    var html2 = html.replace(/<body.*<\/body>/g, '<body></body>');
    var title = $(html2).filter('title').text();
    var idx = title.indexOf('VS');
    if (idx == -1) {
        return;
    }
    host['name'] = title.substr(0, idx).trim();
    guest['name'] = title.substr(idx + 2).trim();
 }

function getStats2(table) {
    return {};
}