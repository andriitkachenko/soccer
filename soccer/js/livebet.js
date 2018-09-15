var globalLastMinutes = 12; // minutes
var globalWatchBlock = true;
var allowNotifications = false;
var getDataFunction = getGameList;

manager = new MatchManager();

var inputTime;
var inputMatchId;
var inputTimeCorrection;
var currentMatchList;

function onLoad() {
    inputTime = $('#startTime');
    inputTimeCorrection = $('#timeCorrection');
    inputMatchId = $('#inputMatch');
    currentMatchList = $('#matchlist');
    setStartTime();
    setNotification(false);
    getDataFunction(date2gameListKey(), manager.setMatches);
    cleanUp();

}

function getMatchId(str) {
    if (!str) {
        return -1;
    }
    var id = str.toLowerCase();
    var id_pos = id.indexOf('id=');
    if ( id_pos != -1) {
        id = id.substr(id_pos + 3);
    }
    if (!isNaN(id)) {
        return id;
    } else {
        return -1;
    }
}

function setStartTime() {
    inputTime.val(makeTimeString(Date.now()));
}

function setMatchPanel(id) {
    var m = manager.getMatch(id);
    inputTime.val(startTime2s(m.getStartTime()));
    inputMatchId.val(m.getMatchId())
    inputTimeCorrection.val(m.getTimeCorrection());
}

function addNewMatch() {
    var id = getMatchId(inputMatchId.val());
    var startTime = time2minutes(inputTime.val());
    var correction = parseInt(inputTimeCorrection.val());
    inputMatchId.val("");
    if (id == -1) {
        return;
    } 
    if (manager.matchExists(id)) {
        manager.getMatch(id).setStartTime(startTime);
        manager.getMatch(id).setTimeCorrection(correction);
        manager.updateList();
        return;
    }
    var game = {
        'id' : id,
        'time' : time2minutes(inputTime.val())
    }
    manager.addMatch(game);
    manager.updateList();
}

function removeMatch(id) {
    manager.removeMatch(id);
}

function setMatchLink(id) {
    inputMatchId.val(baseUrl + id);
}

function updateListView(matchListView){
    currentMatchList.empty().prepend($(matchListView)); 
}

function updateMatchStartTime(id, matchTimeStr) {
    manager.getMatch(id).adjustStartTime(getTimestamp(), parseInt(matchTime));
    manager.updateList();
}

function turnWatching () {
    globalWatchBlock = !globalWatchBlock;
    $('#startButton').html((globalWatchBlock ? 'Start' : 'Stop'));
    $('#startButton').css("background-color", globalWatchBlock ? 'red' : 'lightgreen');
    if (!globalWatchBlock) {
        manager.updateStatus();
    }
//    setNotification(!globalWatchBlock);
}

function allowGlobalUpdate() {
    return  !globalWatchBlock;
}

function sortByHalfShots() {
    manager.sortListByHalfShots();
    manager.updateList();
}

function sortByMatchShots() {
    manager.sortListByMatchShots();
    manager.updateList();
}

function sortByTime() {
    manager.sortListByMatchTime();
    manager.updateList();
}

function sortByLast() {
    manager.sortListByLastShots();
    manager.updateList();
}

function showNotification (stats) {
    if (allowNotifications && !globalWatchBlock) {
        var notification = new Notification("!!! " + stats['host']['name'] + "- " + stats['guest']['name']);
        notification.onshow = function(){setTimeout(function() {notification.close();}, 5000);}
    }
}

function setNotification(allow) {
    if (!"Notification" in window) {
        return;
    }
    var allow2 = allow;
    if (allow && Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission(function (permission) {
            if (permission !== "granted") {
                allow2 = false;
            }
        });
    }

    allowNotifications = allow2;
    $('#notification').css("background-color", allowNotifications ? 'lightgreen' : 'red');
}

function changeNotifications() {
    setNotification(!allowNotifications);
}

function clearList() {
    manager.clearList();
}

function cleanUp() {
    $('body').children('div[id!="main_div"]').remove();
    var w =  $('body').width();
    $('#main_div').css('width', w);
}