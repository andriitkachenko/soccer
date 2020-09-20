var FavMatchKey = "s_topIds";
var Config = {
    //Language: language == "" ? 0 : parseInt(language),
    getTopIds: function () { return unescape(findCookie(FavMatchKey)); },
    saveTopId: function (mIds) {
        writeCookie(FavMatchKey, mIds, new Date((new Date()).getTime() + 30 * 24 * 3600000));
    },
    limitTop: function (mIds) {

    },
    UpdateChangeDuretion: 4000,
    IsTop: 0,
    OddsFormat: 1,
    Init: function () {
        var topIds = this.getTopIds().split(",");
        this.IsTop = topIds.contains(_scheduleId);
        this.OddsFormat = findCookie("oFormat") ? (parseInt(findCookie("oFormat"))) : 1;
        if (this.IsTop) addClass(_$("btnOnTop"), "on");

        //only 7 days duration is enabled to marking top
        if (Math.abs((new Date() - ToLocalTime(mtime)) / 1000 / (24 * 60 * 60)) > 7) {
            _$("btnOnTop").style.display = "none";
        }
    }
};

/*required bomHelper*/
var mslive = {
    loaded: 0,
    state: -1,
    orgModel: null,
    timeDiff: 0,
    timeCountEl: null,
    init: function (strCh) {

        //load difftime
        bomHelper.ajaxGet("/txt/timeGmt.shtml", function (data) {
            var t = new Date(data.substr(0, 4), parseInt(data.substr(4, 2)) - 1, data.substr(6, 2), data.substr(8, 2), data.substr(10, 2), data.substr(10, 2));

            mslive.timeDiff = parseInt(((new Date()).getTime() + ((new Date()).getTimezoneOffset() * 60000) - t.getTime()) / 1000);
        }, false);

        //1514139^3^20180406083000^20180406093824^1^0^1^0^0^0^3^0^0.25^^5^3^1^^5^2
        var mItem = new _glModel.chMatch(strCh);
        mslive.orgModel = mItem;

        _$("liveSt").innerHTML = this.getMatchState(mItem.State, mItem.MatchTime);
        if (mItem.State > 0 || mItem.State == -1)
            _$("liveFt").innerHTML = mItem.hScore + " - " + mItem.gScore;
        else _$("liveFt").innerHTML = "VS";

        if (mItem.State >= 2 || mItem.State == -1)
            _$("liveHt").innerHTML = "(" + mItem.hHalfScore + " - " + mItem.gHalfScore + ")";
        else _$("liveHt").innerHTML = "-";

        if (mItem.mId > 0) {
            setTimeout("mslive.check()", 4000);
            setTimeout("mslive.updatemin()", 5000);
        }

        this.timeCountEl = _$("timeCount");
        if (this.timeCountEl)
            this.updateCountdown();
    },
    updateCountdown: function () {
        if (this.timeCountEl) {
            if (this.orgModel.State != 0) {
                this.timeCountEl.remove();
                this.timeCountEl = null;
            }
            else {
                var duration = Math.round((mslive.orgModel.MatchTime - new Date()) / (60 * 1000));
                this.timeCountEl.innerHTML = _locModel.T.T_FM_CountDown.replace("{0}", duration);
            }

            if (!this.timeCountEl && window.adjustHeadPadding) {
                adjustHeadPadding(1);
            }
        }

        setTimeout("mslive.updateCountdown()", 30000);
    },
    updatemin: function () {
        var mItem = mslive.orgModel;
        if (!mItem || mslive.orgModel.mId <= 0 || mslive.orgModel.State == -1) { return; }
        _$("liveSt").innerHTML = this.getMatchState(mItem.State, mItem.MatchTime);
        setTimeout("mslive.updatemin()", 30000);
    },
    check: function () {
        if (mslive.orgModel.mId <= 0 || mslive.orgModel.State == -1) return;

        var scoreTxt = "/gf/phone/livechange.txt";
        bomHelper.ajaxGet(scoreTxt, this.refresh, 1);

        setTimeout("mslive.check()", 4000);
    },
    oldCh: "",
    refresh: function (data) {
        if (data == "" || data == "!" || mslive.oldCh == data) {
            return;
        }
        mslive.oldCh = data;

        var arrData = data.split(_glModel.SplitRecord);
        var mItem = mslive.orgModel;
        var mState = null;
        var mScore = null;
        var mHt = null;
        var stateChange = 0;
        for (var i = 0; i < arrData.length; i++) {
            var cItem = new _glModel.chMatch(arrData[i]);

            if (cItem.mId == mItem.mId) {
                mItem.mTime = cItem.mTime;
                mItem.mTime2 = cItem.mTime2;
                mItem.MatchTime = cItem.MatchTime;
                if (mItem.DisplayTime != cItem.DisplayTime) {
                    mItem.DisplayTime = cItem.DisplayTime;
                    var em = _$("liveMt");
                    if (em) em.innerHTML = mItem.DisplayTime;
                }

                if (cItem.State != mItem.State) {
                    mItem.State = cItem.State;
                    stateChange = 1;
                    _$("liveSt").innerHTML = mslive.getMatchState(mItem.State, mItem.MatchTime);
                }

                if (cItem.hScore != mItem.hScore || cItem.gScore != mItem.gScore) {

                    mItem.hScore = cItem.hScore;
                    mItem.gScore = cItem.gScore;
                    if (mItem.State > 0 || mItem.State == -1)
                        _$("liveFt").innerHTML = mItem.hScore + " - " + mItem.gScore;
                    else _$("liveFt").innerHTML = "VS";
                }

                if (stateChange || cItem.hHalfScore != mItem.hHalfScore || cItem.gHalfScore != mItem.gHalfScore) {
                    mItem.hHalfScore = cItem.hHalfScore;
                    mItem.gHalfScore = cItem.gHalfScore;
                    if (mItem.State >= 2 || mItem.State == -1)
                        _$("liveHt").innerHTML = "(" + mItem.hHalfScore + " - " + mItem.gHalfScore + ")";
                    else _$("liveHt").innerHTML = " - ";
                }
            }
        }
    },
    close: function (p) {

    },
    getMatchState :function(mState, startTime) {
    var ms = "";
    switch (mState) {
        case 5: ms = _locModel.T.T_Stat_Pen; break;//点球
        case 4: ms = _locModel.T.T_Stat_OverTime; break;//加时
        case 3: ms = _locModel.T.T_Stat_S_Part2; break;//下半场
        case 2: ms = _locModel.T.T_HT; break;//中场
        case 1: ms = _locModel.T.T_Stat_S_Part1; break;//上半场
        case 0: ms = "&nbsp"; break;
        case -1: ms = _locModel.T.T_Stat_Finished; break;//完
        case -10: ms = _locModel.T.T_Stat_Cancel; break;//取消
        case -11: ms = _locModel.T.T_Stat_Pending; break;//待定
        case -12: ms = _locModel.T.T_Stat_Abd; break;//腰砍
        case -13: ms = _locModel.T.T_Stat_Pause; break;//中断
        case -14: ms = _locModel.T.T_Stat_Postpone; break;//推迟
    }
    var mIcon = "<i class='mit'><img src='/images/com/in_w.gif'></i>";
    if (mState == 1) {
        var now = new Date();
        var serverTime = now.getTime() / 1000 - this.timeDiff;

            var df = (serverTime - startTime.getTime() / 1000) / 60;
            df = parseInt(df);
            if (df <= 0) {
                ms = "1" + mIcon;
            } else if (df <= 45) {
                ms = df.toString() + mIcon;
            } else {
                ms = "45+" + mIcon;
            }
        } else if (mState == 3) {
            var now = new Date();
            var serverTime = now.getTime() / 1000 - this.timeDiff;
            var df = (serverTime - startTime.getTime() / 1000) / 60 + 46;
            //由于不确定它计算出来的数据一定准确，所以做多几个判断
            df = parseInt(df);
            if (df <= 46) {
                ms = "46" + mIcon;
            } else if (df <= 90) {
                ms = df.toString() + mIcon;
            } else {
                ms = "90+" + mIcon;
            }
        }
        return ms;
    },
    showExplainList: function (hname, gname) {
        var exInfo = _$("exlist");
        if (!exInfo) return;

        exInfo.innerHTML = explainList(exInfo.getAttribute("data-ex"), hname, gname);
        setTimeout("adjustHeadPadding(1);", 200);
    }
};

var _glModel = new Object();
_glModel.SplitColumn = "^";
_glModel.SplitDomain = "$$";
_glModel.SplitRecord = "!";

function ToLocalTime(t, type) {
    //20200809143000
    var strTime = "";
    var t1 = new Date(t.substr(0, 4), parseInt(t.substr(4, 2).replace(/0(\d)/, "$1")) - 1, t.substr(6, 2), t.substr(8, 2), t.substr(10, 2));
    var localT = new Date(t1.getTime() - ((new Date()).getTimezoneOffset() * 60000) - (8 * 3600000));

    if (!type) {//20150101123000
        strTime = localT;
    }
    else if (type == 1) {
        strTime = ToDateString(localT) + " " + ToTimeString(localT);
    }
    else if (type == 2) {
        strTime = ToDateString(localT);
    }
    else if (type == 3) {
        strTime = ToTimeString(localT);
    }
    else if (type == 4) {
        strTime = ToDateTimeString(localT);
    }
    else if (type == 5) {
        strTime = ToFullDate(localT);
    }
    else if (type == 6) {
        strTime = ToShortDateString(localT);
    }

    if (strTime) {
        return strTime;
    }

    return t;
}


_glModel.chMatch = function (infoStr) {
    //1840264^3^20200809143000^20200809153632^2^2^1^0^0^0^0^0^^^^^"
    var infoArr = infoStr.split(_glModel.SplitColumn);
    this.mId = infoArr[0];
    this.State = parseInt(infoArr[1]);
    this.mTime = infoArr[2];
    this.mTime2 = infoArr[3];
    if (infoArr[3] != "")
        this.MatchTime = ToLocalTime(infoArr[3]);
    else
        this.MatchTime = ToLocalTime(infoArr[2]);

    this.StartTime = ToLocalTime(infoArr[2]);
    this.DisplayTime = ToLocalTime(infoArr[2], 4);

    this.hRed = infoArr[8];
    this.gRed = infoArr[9];
    this.hYellow = infoArr[10];
    this.gYellow = infoArr[11];
    this.hScore = infoArr[4];
    this.gScore = infoArr[5];
    this.hHalfScore = infoArr[6];
    this.gHalfScore = infoArr[7];
    this.explain = infoArr[13];

    this.hCorner = infoArr[14];
    this.gCorner = infoArr[15];
    this.hasCorner = infoArr[16] == "1";
    if (this.hasCorner) {
        if (!this.hCorner) this.hCorner = "0";
        if (!this.gCorner) this.gCorner = "0";
    }
}

function toggleFav() {
    var mId = _scheduleId;
    Config.IsTop = !Config.IsTop;
    var topIds = Config.getTopIds().split(",");
    if (Config.IsTop)
        topIds.push(mId);
    else {
        var idx = topIds.indexOf(mId.toString());
        topIds.splice(idx, 1);
    }
    Config.saveTopId(topIds.join(","));

    toggleClass(_$("btnOnTop"), "on");
}

function toggleShare() {
    var content = _$("shareTpl").innerHTML;
    popUtil.open(popUtil.option(_locModel.T.T_Share, content, 4).extend({ position: 2 }));
}

function copyLink(url) {
    var txtUrl = document.createElement("textarea");
    txtUrl.innerHTML = url || window.location.href;
    document.body.appendChild(txtUrl);
    txtUrl.select();
    document.execCommand("Copy");
    txtUrl.remove();
    popUtil.open(popUtil.option(_locModel.T.T_Share, _locModel.T.T_LinkCopied, 1));
}