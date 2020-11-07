var runningRequest = false;
var baseUrl = "php/statistics.php";

// load statistics
function getStatistics (matchId, onResponse) {
    var runningRequest = true;
    $.ajax({
        type: 'POST',
        url: baseUrl,
        data: {
            match_id : matchId
        },
        success: function(response) {
            var stats = null;
            try {
                stats = JSON.parse(response);
            } catch (e) {}
            if (stats) {
                onResponse(stats);
            }
            runningRequest = false;
        },
        error : function () {
            onResponse(null);
            runningRequest = false;
        }
    });
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
