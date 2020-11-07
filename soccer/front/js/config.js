server = window.location.hostname.indexOf('livesoccer') != -1;

const MIN_WATCHING_TIME = 10;
const MAX_WATCHING_TIME = 99;
const MAX_START_WATCHING_TIME = 85;
const MIN_CLEAR_TIME = 20;
const BREAK_START = 45;
const BREAK_END = 60;

const MATCH_PART_HALF1 = 0;
const MATCH_PART_BREAK = 1;
const MATCH_PART_HALF2 = 2;

dev_mode = window.location.href.indexOf('livebet_dev') != -1;
