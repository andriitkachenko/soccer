server = window.location.hostname.indexOf('scoreslive') != -1 
        || window.location.hostname.indexOf('soccerlive') != -1 
        || window.location.hostname.indexOf('aatkachenko') != -1 ;

const MIN_WATCHING_TIME = 20;
const MAX_WATCHING_TIME = 90;
const MAX_START_WATCHING_TIME = 80;
const MIN_CLEAR_TIME = 20;
const BREAK_START = 45;
const BREAK_END = 65;

dev_mode = window.location.href.indexOf('livebet_dev') != -1;
