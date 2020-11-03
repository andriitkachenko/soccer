<?php

const EVENT_GOAL              = "gl";
const EVENT_SHOT              = "sh";
const EVENT_SHOT_ON_GOAL      = "sg";
const EVENT_FOUL              = "fl";
const EVENT_CORNER_KICK       = "ck";
const EVENT_OFFSIDE           = "of";
const EVENT_YELLOW_CARD       = "yc";
const EVENT_RED_CARD          = "rc";
const EVENT_BALL_POSSESSION   = "bp";
const EVENT_HEADER            = "hd";
const EVENT_SAVE              = "sv";
const EVENT_SUCCESSFULL_TACKLE= "st";
const EVENT_INTERCEPTION      = "ic";
const EVENT_ASSIST            = "as";

const GAME_EVENT_CODES = [
    EVENT_GOAL,
    EVENT_SHOT,
    EVENT_SHOT_ON_GOAL,
    EVENT_FOUL,
    EVENT_CORNER_KICK,
    EVENT_OFFSIDE,
    EVENT_YELLOW_CARD,
    EVENT_RED_CARD,
    EVENT_BALL_POSSESSION,
    EVENT_HEADER,
    EVENT_SAVE,
    EVENT_SUCCESSFULL_TACKLE,
    EVENT_INTERCEPTION,
    EVENT_ASSIST
];

function event2code($event) {
    $event = strtolower($event);
    $code = false;
    switch($event) {
        // goal do not have name in statistics html
        case "shots":              $code = EVENT_SHOT; break;
        case "shots on goal":      $code = EVENT_SHOT_ON_GOAL; break;
        case "fouls":              $code = EVENT_FOUL; break;
        case "corner kicks":       $code = EVENT_CORNER_KICK; break;
        case "offsides":           $code = EVENT_OFFSIDE; break;
        case "yellow cards":       $code = EVENT_YELLOW_CARD; break;
        case "red cards":          $code = EVENT_RED_CARD; break;
        case "ball possession":    $code = EVENT_BALL_POSSESSION; break;
        case "headers":            $code = EVENT_HEADER; break;
        case "saves":              $code = EVENT_SAVE; break;
        case "successful tackles": $code = EVENT_SUCCESSFULL_TACKLE; break;
        case "interceptions":      $code = EVENT_INTERCEPTION; break;
        case "assists":            $code = EVENT_ASSIST; break;
    }
    return $code;
}

?>