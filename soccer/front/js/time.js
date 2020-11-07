function makeTimeString (milliseconds) {
    var time = new Date(milliseconds);
    var h = time.getHours();
    var m = time.getMinutes();
    m = Math.floor(m / 5) * 5;
    h = (parseInt(h) < 10 ? "0" : "") + h; 
    m = (parseInt(m) < 10 ? "0" : "") + m; 
    return h + ":" + m;
}

function time2minutes(timestring) {
    var time = new Date();
    var y = time.getFullYear();
    var m = time.getMonth() + 1;
    var d = time.getDate();
    m = (parseInt(m) < 10 ? "0" : "") + m; 
    d = (parseInt(d) < 10 ? "0" : "") + d; 
    time = Date.parse(y + "-" + m + "-" + d + "T" + timestring + 'Z');
    return getMinutes(time.valueOf());
}

function startTime2s(minutes) {
    return makeTimeString(minutes * 60 * 1000);
}

function getMinutes(milliseconds) {
    return Math.floor(milliseconds / 1000 / 60);
}

function date2gameListKey() {
    var time = new Date();
    var m = time.getMonth() + 1;
    var d = time.getDate();
    m = (parseInt(m) < 10 ? "0" : "") + m; 
    d = (parseInt(d) < 10 ? "0" : "") + d; 
    return m + d;
}

function getTimestamp() {
    return Math.floor(Date.now() / 1000. / 60.);
}