var globalLastMinutes = 12; // minutes
var globalWatchBlock = true;
var allowNotifications = false;
var getDataFunction = getGameList;

manager = new MatchManager();

var inputMatchTime;
var currentMatchList;

function onLoad() {
    inputMatchTime = $('input#matchTime');
    currentMatchList = $('#matchlist');
    setNotification(false);
    toggleSort();
    getDataFunction(date2gameListKey(), function(gameList) {
        manager.setMatches(gameList);
        manager.restoreFromStorage();
        toggleWatching();
    });
}

function onUnload() {
    manager.saveToStorage();
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

var timePanelTimeout;

function hideTimePanel() {
    clearTimeout(timePanelTimeout);
    inputMatchTime.css('display', 'none');
}

function setTimePanel(id) {
    var m = manager.getMatch(id);
    var time = getTimestamp();
    time = m.getMatchTime(time);
    if (inputMatchTime.css('display') === 'none') {
        inputMatchTime.val(time);
        inputMatchTime.css('display', 'block');
        clearTimeout(timePanelTimeout);
        timePanelTimeout = setTimeout(hideTimePanel, 15000);
        return;
    } 
    var newTime = parseInt(inputMatchTime.val());
    if (isNaN(newTime)) {
        return;
    }
    var correction = time - newTime;
    var breakTime = BREAK_END - BREAK_START;
    if (newTime < 45 && time > 45)
        correction += breakTime;
    if (newTime > 45 && time < 45)
        correction -= breakTime;
    m.setTimeCorrection(correction);
    hideTimePanel();
    $('div.match#' + id + ' td.time').html(newTime);
    hideTimePanel();
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

function toggleWatching() {
    globalWatchBlock = !globalWatchBlock;
    $('#startButton').html((globalWatchBlock ? 'Start' : 'Stop'));
    $('#startButton').css("background-color", globalWatchBlock ? 'red' : 'lightgreen');
    manager.toggleUpdate(globalWatchBlock);
//    setNotification(!globalWatchBlock);
}

function allowGlobalUpdate() {
    return  !globalWatchBlock;
}

function sortByHalfShots() {
    manager.setSortByHalfShots();
    manager.updateList();
}

function sortByMatchShots() {
    manager.setSortByMatchShots();
    manager.updateList();
}

function sortByTime() {
    manager.setSortByMatchTime();
    manager.updateList();
}

function sortByLast() {
    manager.setSortByLastShots();
    manager.updateList();
}

var browserNotification;

function closeNotification() {
    if (browserNotification) {
        browserNotification.close();
    }
}
function showNotification (notif) {
    if (notif && allowNotifications && !globalWatchBlock) {
        var options  = {
            body : notif
        }
        closeNotification();
        browserNotification = new Notification("Live Update", options);
        setTimeout(closeNotification, 8000);
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

function openDataPage() {
    window.open('./php/parsehub_run.php', '_blank');
}

function toggleFilter() {
    manager.toggleMatchFilter();
    var filter = manager.getMatchFilter();
    if (filter) {
        $('button#filter').addClass('active');
    } else {
        $('button#filter').removeClass('active');
    }
    manager.updateList();
}

function toggleSort() {
    var btn = $('button#sort');
    var sorted = btn.hasClass('active');
    if (sorted) {
        sortByTime();
        btn.removeClass('active');
    } else {
        sortByHalfShots();
        btn.addClass('active');
    }
}

function saveMatchList() {
    manager.saveMatchListToCache();
}