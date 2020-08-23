var _isdebug = 0;
function traceError() {
    window.onerror = function (message, url, line, column, error) {
        alert('traceError:' + "msg:" + message + "line:" + line);
    }
}

if (location.href.indexOf('debug') != -1 || _isdebug) {
    _isdebug = 1;
    traceError();
}

var ShowAd = true;
var _language = 0;//0:ng,1:th,2:vn,3:bola
var _adNoFollow = 0;
function BomHelper() {
    this.ie = "";
    this.firefox = "";
    this.chrome = "";
    this.opera = "";
    this.safari = "";
    this.operaMini = "";
    this.uc = "";
    this.ios = "";
    this.android = {};
    this.pc = "";
    this.kv = function (k, v, i) {
        return { key: k, value: v, opt: i || 1 };
    };
}

//检测浏览器版本，并保存
BomHelper.prototype.checkBrowerType = function () {
    var ua = navigator.userAgent.toLowerCase(), s, rkv;
    var rdic = [
        this.kv('ie', /msie ([\d.]+)/),
        this.kv('qq', /qq\/([\d.]+)/i),
        this.kv('firefox', /firefox\/([\d.]+)/i),
        this.kv('uc', /ucbrowser\/([\d.]+)/i),
        this.kv('operaMini', /opera[\s]mini.([\d.]+)/i),
        this.kv('opera', /opera[\s]mini.([\d.]+)/i),
        this.kv('chrome', /chrome\/([\d.]+)/i),//chrome for android
        this.kv('crios', /crios\/([\d.]+)/i),//chrome for ios
        this.kv('safari', /version\/([\d.]+).*safari/i),
    ];

    for (var i = 0; i < rdic.length; i++) {
        rkv = rdic[i];
        if ((s = ua.match(rkv.value))) {
            this[rkv.key] = s[1];
            break;
        }
    }

    if (/(iphone|ipad|ipod|ios)/i.test(ua)) {
        this.ios = (s = ua.match(/(?:iphone|ipad|itouch).* os (\d+)_[\d]/)) ? s[1] : "1";
    }
    else if (/android/i.test(ua)) {
        this.android.version = ((s = ua.match(/(?:android) ([\d\.]+);/))) ? s[1] : "1";

        rdic = [
            this.kv('huawei', /(huawei|honorbln)/i),
            this.kv('oppo', /oppo/i),
            this.kv('vivo', /vivo/i),
            this.kv("xiaomi", /(miui|xiaomi)/i),
            this.kv("meizu", /;[\s]+mz-/i),
            this.kv("original", /android/),
        ];

        for (var i = 0; i < rdic.length; i++) {
            rkv = rdic[i];
            if ((s = ua.match(rkv.value))) {
                this.android[rkv.key] = 1;
                break;
            }
        }
    }
    else this.pc = "pc";
}

//获取ajax对象
BomHelper.prototype.ajaxObj = function () {
    var xmlHttp = null;

    if (this.ie != "") {
        if (typeof ActiveXObject != "undefined") {
            return new XMLHttpRequest();
        }
        else {
            try {
                xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (ex1) {
                try {
                    xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (ex2) {
                    try {
                        xmlHttp = new XMLHttpRequest();
                    } catch (ex3) {
                        alert("创建ajax对象失败,本网站只支持ie6以上版本浏览器,请刷新页面重试");
                    }
                }
            }
        }
    } else {
        try {
            xmlHttp = new XMLHttpRequest();
        } catch (ex3) {
            alert("创建ajax对象失败,请刷新页面重试");
        }
    }
    return xmlHttp;
}
//发送ajax的GET请求
BomHelper.prototype.ajaxGet = function (sUrl, fnAjax, isdefer) {
    if (isdefer == undefined) isdefer = true;
    var xmlHttp = this.ajaxObj();
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.status == 404) return;

        if (xmlHttp.readyState == 4)
            fnAjax(xmlHttp.responseText);
    }
    if (sUrl.indexOf("?") == -1)
        sUrl = sUrl + "?flesh=" + Math.random();
    else
        sUrl = sUrl + "&flesh=" + Math.random();
    xmlHttp.open("GET", sUrl, isdefer);
    xmlHttp.send(null);
}

//发送ajax的post请求
BomHelper.prototype.ajaxPost = function (sUrl, sPostData, fnAjax) {
    var xmlHttp = this.ajaxObj();

    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4)
            fnAjax(xmlHttp.responseText);
    }
    if (sPostData == "")
        sPostData = sPostData + "flesh=" + Math.random();
    else
        sPostData = sPostData + "&flesh=" + Math.random();
    xmlHttp.open("POST", sUrl, true);
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlHttp.send(sPostData);
}

//同步获取xml文件
BomHelper.prototype.ajaxXml = function (sUrl, sys, fnAjax) {
    var xmlHttp = this.ajaxObj();
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.status == 404) return;

        if (xmlHttp.readyState == 4)
            fnAjax(xmlHttp.responseXML, xmlHttp.responseText);
    }
    if (sUrl.indexOf("?") == -1)
        sUrl = sUrl + "?flesh=" + Math.random();
    else
        sUrl = sUrl + "&flesh=" + Math.random();
    xmlHttp.open("GET", sUrl, sys);
    xmlHttp.send(null);
}

//若是IE7以上版本，则要求它使用IE7
BomHelper.prototype.useIE7 = function () {
    document.write("<meta content=\"IE=EmulateIE7\" http-equiv=\"X-UA-Compatible\">");
}

var bomHelper = new BomHelper();
bomHelper.checkBrowerType();

//JS去除空格
String.prototype.Trim = function () {
    return this.replace(/(^\s*)|(\s*$)/g, "");
}

Array.prototype.contains = function (obj) {
    var i = this.length;
    while (i--) {
        if (this[i] == obj) {
            return true;
        }
    }
    return false;
}

//js Cookie操作
function findCookie(cookieName) {
    //获取cookie字符串 
    var strCookie = document.cookie;
    //将多cookie切割为多个名/值对 
    var arrCookie = strCookie.split("; ");
    var cookieValue = "";
    //遍历cookie数组，处理每个cookie对 
    for (var i = 0; i < arrCookie.length; i++) {
        var arr = arrCookie[i].split("=");
        //找到名称为userId的cookie，并返回它的值 
        if (cookieName == arr[0]) {
            cookieValue = arr[1];
            break;
        }
    }
    return cookieValue;
}

function writeCookie(name, value, expireVal) {
    var expire = expireVal;
    var hours = 365 * 24;
    if (expire == undefined)
        expire = new Date((new Date()).getTime() + hours * 3600000);

    expire = ";path=/;expires=" + expire.toGMTString();
    document.cookie = name + "=" + escape(value) + expire;
}

function delCookie(name) {
    var date = new Date();
    date.setTime(date.getTime() - 10000);
    document.cookie = name + "=a; expires=" + date.toGMTString();
}

var Storage = {
    enableStorage: function (st) { try { st.setItem("_test", '1'); var ret = (st.getItem("_test") == '1'); st.removeItem("_test"); return ret; } catch (e) { return false; } },
    enableLocal: function () { return (typeof (localStorage) != "undefined" && this.enableStorage(localStorage)); },
    enableSession: function () { return (typeof (sessionStorage) != "undefined" && this.enableStorage(sessionStorage)); },
    getLocal: function (k) {
        if (this.enableLocal())
            return localStorage.getItem(k);
        else { return unescape(findCookie(k)); }
    },
    setLocal: function (k, v) {
        if (this.enableLocal())
            localStorage.setItem(k, v);
        else { writeCookie(k, v); }
    },
    getSession: function (k) {
        if (this.enableSession())
            return sessionStorage.getItem(k);
        else return this.getLocal(k);
    },
    setSession: function (k, v) {
        if (this.enableSession())
            sessionStorage.setItem(k, v);
        else this.setLocal(k, v);
    }
};

//20150101123000 or 2015,01,01,12,30,00
function getTimeByUtcStrNum(t) {
    t = t.trim();
    var t1 = t.split(/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/);

    var reg2 = /^(\d+),(\d+),(\d+),(\d+),(\d+),(\d+)$/;
    if (t1.length == 1 && reg2.test(t)) {
       return getTimeByUtcStr(t);
    }

    var t2 = new Date(t1[1], parseInt(t1[2]) - 1, t1[3], t1[4], t1[5], t1[6]);
    t2 = new Date(Date.UTC(t2.getFullYear(), t2.getMonth(), t2.getDate(), t2.getHours(), t2.getMinutes(), t2.getSeconds()));
    return t2;
}

//2015,01,01,12,30,00
function getTimeByUtcStr(val) {
    var t = splitTime(val);
    var timeZoneHour = -((new Date()).getTimezoneOffset() / 60);
    var t2 = new Date(t[0], t[1], t[2], t[3], t[4], t[5]);
    t2 = new Date(t2.getFullYear(), t2.getMonth(), t2.getDate(), t2.getHours(), t2.getMinutes(), t2.getSeconds());
    t2 = new Date(t2.getTime() + (timeZoneHour * 60 * 60000));
    return t2;
}

//兼容三种日期格式[2019,4,30,07,00,00],[2019,5-1,30,07,00,00],[2019-4-30 07:00:00]
function splitTime(t) {
    var d = 0;
    if (t.indexOf(':') != -1) {
        t = t.replace(/(-|\s|:)/g, ",");
        d = 1;
    }
    var t = t.split(",");
    t[1] = eval(t[1]) - d;
    return t;
}

//2019-5-30 9:04
function getTimeByUtcForamted(val) {
    var timeZoneHour = -((new Date()).getTimezoneOffset() / 60);
    var t = val.split(/([\d]+)\-([\d]+)\-([\d]+)\s([\d]+)\:([\d]+)/);
    var t2 = new Date(t[1], parseInt(t[2] - 1), t[3], t[4], t[5]);
    t2 = new Date(t2.getFullYear(), t2.getMonth(), t2.getDate(), t2.getHours(), t2.getMinutes(), t2.getSeconds());
    t2 = new Date(t2.getTime() + (timeZoneHour * 60 * 60000));
    return t2;
}

var _locModel = _locModel || { T: 0 };
var months = new Array(
    _locModel.T.T_January || "January",
    _locModel.T.T_February || "February",
    _locModel.T.T_March || "March",
    _locModel.T.T_April || "April",
    _locModel.T.T_May || "May",
    _locModel.T.T_June || "June",
    _locModel.T.T_July || "July",
    _locModel.T.T_August || "August",
    _locModel.T.T_September || "September",
    _locModel.T.T_October || "October",
    _locModel.T.T_November || "November",
    _locModel.T.T_December || "December");

function formatDateForDou(t, type) {
    var strTime = "";
    var t2 = getTimeByUtcStr(t);

    if (type == 1)
        strTime = ToDateTimeString(t2);

    document.write(strTime);
}

function formatDate(t, type) {
    var strTime = "";
    t = t.trim();
    var t1 = t.split(/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/);

    var reg2 = /^(\d+),(\d+),(\d+),(\d+),(\d+),(\d+)$/;
    if (t1.length == 1 && reg2.test(t)) {
        formatDateForDou(t, type);
        return;
    }

    var t2 = new Date(t1[1], parseInt(t1[2]) - 1, t1[3], t1[4], t1[5], t1[6]);
    t2 = new Date(Date.UTC(t2.getFullYear(), t2.getMonth(), t2.getDate(), t2.getHours(), t2.getMinutes(), t2.getSeconds()));

    if (type == 1)
        strTime = t2.getFullYear();
    else if (type == 2)
        strTime = ToDateString(t2);
    else if (type == 3) {
        strTime = ToTimeString(t2);
    }
    else if (type == 4) {
        strTime = ToMonthNameDate(t2);
    }
    else if (type == 5) {
        strTime = ToDateTimeString(t2);
    }
    else strTime = ToFullDate(t2);

    document.write(strTime);
}

function formatZero(val) {
    return (val < 10) ? '0' + val : val;
}

function writeLocalTime(t, type) {
    var strTime = ToLocalTime(t, type);
    document.write(strTime);
}

function ToLocalTime(t, type) {
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

function ToTimeString(t) {
    var hours = formatZero(t.getHours());
    var min = formatZero(t.getMinutes());

    return hours + ":" + min;
}

function ToDateString(t) {
    var month = formatZero(t.getMonth() + 1);
    var day = formatZero(t.getDate());

    return month + "-" + day;
}

function ToShortDateString(t) {
    var month = formatZero(t.getMonth() + 1);
    var day = formatZero(t.getDate());

    return t.getFullYear().toString().substr(2, 2) + "-" + month + "-" + day;
}

function ToFullDate(t) {
    var month = formatZero(t.getMonth() + 1);
    var day = formatZero(t.getDate());

    return t.getFullYear() + "-" + month + "-" + day;
}

function ToMonthNameDate(t) {
    return t.getDate() + " " + months[t.getMonth()] + " " + t.getFullYear();
}

function ToSimpMonthNameDate(t) {
    return _months2[t.getMonth()] + "." + t.getDate();
}

var _months2 = new Array(
    _locModel.T.T_S_January || "Jan",
    _locModel.T.T_S_February || "Feb",
    _locModel.T.T_S_March || "Mar",
    _locModel.T.T_S_April || "Apr",
    _locModel.T.T_S_May || "May",
    _locModel.T.T_S_June || "Jun",
    _locModel.T.T_S_July || "Jul",
    _locModel.T.T_S_August || "Aug",
    _locModel.T.T_S_September || "Sep",
    _locModel.T.T_S_October || "Oct",
    _locModel.T.T_S_November || "Nov",
    _locModel.T.T_S_December || "Dec");

var _months = new Array(
    _locModel.T.T_January || "January",
    _locModel.T.T_February || "February",
    _locModel.T.T_March || "March",
    _locModel.T.T_April || "April",
    _locModel.T.T_May || "May",
    _locModel.T.T_June || "June",
    _locModel.T.T_July || "July",
    _locModel.T.T_August || "August",
    _locModel.T.T_September || "September",
    _locModel.T.T_October || "October",
    _locModel.T.T_November || "November",
    _locModel.T.T_December || "December");

var _weeks = new Array(_locModel.T.T_Sunday || "Sunday",
    _locModel.T.T_Monday || "Monday",
    _locModel.T.T_Tuesday || "Tuesday",
    _locModel.T.T_Wednesday || "Wednesday",
    _locModel.T.T_Thursday || "Thursday",
    _locModel.T.T_Friday || "Friday",
    _locModel.T.T_Saturday || "Saturday");

var _weeks2 = new Array(_locModel.T.T_S_Sunday || "Sun.",
    _locModel.T.T_S_Monday || "Mon.",
    _locModel.T.T_S_Tuesday || "Tue.",
    _locModel.T.T_S_Wednesday || "Wed.",
    _locModel.T.T_S_Thursday || "Thu.",
    _locModel.T.T_S_Friday || "Fri.",
    _locModel.T.T_S_Saturday || "Sat.");

function ToDateTimeString(t, type) {
    if (type == 1) {
        return _months[t.getMonth()] + " " + t.getDate() + ". " + _weeks[t.getDay()];
    }
    else
        return ToDateString(t, 1) + "," + t.getFullYear() + " " + ToTimeString(t);
}


function timeToText(t2, type) {
    type = type || 0;
    var fmts = [
        "yyyy-MM-dd hh:mm:ss",/*0*/
        "yyyy-MM-dd",/*1*/
        "MM/dd",/*2*/
        "hh:mm",/*3*/
        "t2 dd",/*4*/
        "MM-dd hh:mm",/*5*/
        "yy-MM-dd",/*6*/
        "yyyy",/*7*/
        "t2.dd",/*8*/
        "dd-MM,yyyy hh:mm"/*9*/
    ];

    return dateFtt(fmts[type] || fmts[0], t2);
}

function setTimeByFormat() {
    var elems = document.querySelectorAll("[data-time]");
    for (var i = 0; i < elems.length; i++) {
        var elem = elems[i], t2;
        if (!elem.innerHTML.trim() || elem.getAttribute("data-f2t")) {
            var f2t = elem.getAttribute("data-f2t");
            if (f2t == "2") {
                //20190720184500
                t2 = getTimeByUtcStrNum(elem.getAttribute("data-time"));
            }
            else {
                t2 = getTimeByUtcStr(elem.getAttribute("data-time"));
            }

            
            elem.innerHTML = timeToText(t2, elem.getAttribute("data-fmt"));
            elem.setAttribute("data-time-f", elem.getAttribute("data-time"));
            elem.removeAttribute("data-time");
        }
    }
}

function dateFtt(fmt, t) {
    var o = {
        "M+": t.getMonth() + 1,//month   
        "d+": t.getDate(),//day   
        "h+": t.getHours(),//hours   
        "m+": t.getMinutes(),//minutes
        "s+": t.getSeconds(),//second
        "t1": _months[t.getMonth()],//month name
        "t2": _months2[t.getMonth()],//simp month
        "w1": _weeks[t.getDay()],//week
        "w2": _weeks2[t.getDay()],//simp week

    };

    if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (t.getFullYear() + "").substr(4 - RegExp.$1.length));

    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1 || /[tw]/.test(k)) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));

    return fmt;
}

var GoalCn = ["0", "0/0.5", "0.5", "0.5/1", "1", "1/1.5", "1.5", "1.5/2", "2", "2/2.5", "2.5", "2.5/3", "3", "3/3.5", "3.5", "3.5/4", "4", "4/4.5", "4.5", "4.5/5", "5", "5/5.5", "5.5", "5.5/6", "6", "6/6.5", "6.5", "6.5/7", "7", "7/7.5", "7.5", "7.5/8", "8", "8/8.5", "8.5", "8.5/9", "9", "9/9.5", "9.5", "9.5/10", "10", "10/10.5", "10.5", "10.5/11", "11", "11/11.5", "11.5", "11.5/12", "12", "12/12.5", "12.5", "12.5/13", "13", "13/13.5", "13.5", "13.5/14", "14"];
var GoalCn2 = ["0", "-0/0.5", "-0.5", "-0.5/1", "-1", "-1/1.5", "-1.5", "-1.5/2", "-2", "-2/2.5", "-2.5", "-2.5/3", "-3", "-3/3.5", "-3.5", "-3.5/4", "-4", "-4/4.5", "-4.5", "-4.5/5", "-5", "-5/5.5", "-5.5", "-5.5/6", "-6", "-6/6.5", "-6.5", "-6.5/7", "-7", "-7/7.5", "-7.5", "-7.5/8", "-8", "-8/8.5", "-8.5", "-8.5/9", "-9", "-9/9.5", "-9.5", "-9.5/10", "-10"];

function Goal2GoalT(goal) { //handicap conversion
    if (goal === "")
        return "&nbsp;";
    else {
        if (goal >= 0) return GoalCn[parseInt(goal * 4)] || goal;
        else return GoalCn2[Math.abs(parseInt(goal * 4))] || goal;
    }
}

function getTopHeight() {
    var adTop = 0;
    if (document.documentElement && document.documentElement.scrollTop)
        adTop = document.documentElement.scrollTop;
    else if (document.body)
        adTop = document.body.scrollTop
    else
        adTop = window.pageYOffset;

    return adTop;
}

var _alwaysShowRt = 1;
function returnTop() {
    var top = getTopHeight();
    var rt = document.getElementById("returnTop");
    if (!_alwaysShowRt) {
        _alwaysShowRt = rt.getAttribute("data-always") || 1;
    }
    if (_alwaysShowRt == 2) {
        rt.style.display = "";
        return;
    }

    if (top > 150) {
        rt.style.display = "";
    }
    else rt.style.display = "none";
    setTimeout("returnTop();", 100);
}

var _czc = _czc || [];

var _lazzyLoadCnzz = false;
var _cnzzLoaded = false;


function CzcApi(category, action, label) {
    if (_czc)
        _czc.push(["_trackEvent", category, action, label]);
};

function replaceScript(container, url) {
    var s = document.createElement("script");
    s.type = "text/javascript";
    s.src = url;
    container.removeChild(container.firstChild);
    container.appendChild(s, "script");
}

function addScript(container, url) {
    var s = document.createElement("script");
    s.src = url;
    container.appendChild(s, "script");
}

function toggle() {
    for (var i = 0; i < arguments.length; i++) {
        var elem = _$(arguments[i]);
        if (elem) elem.style.display = elem.style.display == "none" ? "" : "none";
    }
}

function _$(id) {
    return document.getElementById(id);
}

function _$$(q, o) {
    if (typeof (q) !== 'string' || q == '') return [];
    var ss = q.split(' ');
    var attr = '';
    var s = ss[0].split(':')[0];
    if (s != ss[0])
        attr = ss[0].split(':')[1];

    var val = s.split('[')[0];
    if (val != s)
        val = s.split('[')[1].replace(/[",\]]/g, '');
    else
        val = '';
    s = s.split('[')[0];

    var obj = [];
    var sObj = null;

    o = o || document;
    switch (s.charAt(0)) {
        case '#':
            sObj = document.getElementById(s.substr(1));
            if (sObj) obj.push(sObj);
            break;
        case '.':
            var l = o.getElementsByTagName('*');
            var c = s.substr(1);
            for (var i = 0; i < l.length; i++)
                if (l[i].className.search('\\b' + c + '\\b') != -1) obj.push(l[i]);
            break;
        default:
            obj = o.getElementsByTagName(s);
            break;
    }

    if (val) {
        var l = [];
        var a = val.split('=');
        for (var i = 0; i < obj.length; i++) {
            switch (a.length) {
                case 1: if (obj[i].getAttribute(a[0]) != null) l.push(obj[i]); break;
                case 2: if (obj[i].getAttribute(a[0]) == a[1]) l.push(obj[i]); break;
            }
        }
        obj = l;
    }

    if (attr) {
        var l = [];
        for (var i = 0; i < obj.length; i++)
            if (obj[i][attr]) l.push(obj[i]);
        obj = l;
    }

    if (ss.length > 1) {
        var l = [];
        for (var i = 0; i < obj.length; i++) {
            var ll = arguments.callee(q.substr(ss[0].length + 1), obj[i]);
            if (ll.tagName) l.push(ll);
            else
                for (var j = 0; j < ll.length; j++) l.push(ll[j]);
        }
        obj = l;
    }

    if (sObj && ss.length == 1) {
        obj = sObj;
        if (obj) obj.length = 1;
    } else {
        var l = [];
        for (var i = 0; i < obj.length; i++) obj[i].$isAdd = false;
        for (var i = 0; i < obj.length; i++) {
            if (!obj[i].$isAdd) {
                obj[i].$isAdd = true;
                l.push(obj[i]);
            }
        }
        obj = l;
    }

    return obj;
}

function hasClass(elem, cls) {
    if (elem)
        return elem.classList.contains(cls);

    return false;
}

function removeClass(elem, cls) {
    if (elem) {
        elem.classList.remove(cls);
    }
}

function addClass(elem, cls) {
    if (elem) {
        elem.classList.add(cls);
    }
}

function toggleClass(elem, cls) {
    if (typeof (elem) == "string") elem = _$(elem);

    if (elem) {
        elem.classList.toggle(cls);
    }
}

function switchNavigate() {
    if (_$("navPop").style.display == "none") {
        _$("navPop").style.display = "";
        document.body.parentNode.style.overflow = "hidden";
        //safari
        document.body.style.overflow = "hidden";
    } else {
        _$("navPop").style.display = "none";
        document.body.parentNode.style.overflow = "visible";
        //safari
        document.body.style.overflow = "visible";
    }
}

function explainList(exList, homeTeam, awayTeam) {
    if (!exList || exList == "")
        return "";
    var exText = new Array();
    //得分
    var gex4 = exList.split(";");
    var explainTemp = "";
    if (gex4[0] != "")
        exText.push(gex4[0].replace(",", (_locModel.T.T_Ex_Min || "Min") + "[") + "]");
    if (gex4[1] != "")
        exText.push((_locModel.T.T_Ex_TwoRounds || "Two Rounds") + "[" + gex4[1] + "]");
    if (gex4[2] != "")
        exText.push(gex4[2].replace("1,", "120" + (_locModel.T.T_Ex_Min || "Min") + "[").replace("2,", (_locModel.T.T_Ex_Ot || "Ot") + "[").replace("3,", (_locModel.T.T_Ex_Ot || "Ot") + "\'[") + "]");
    if (gex4[3] != "")
        exText.push((_locModel.T.T_Ex_Pen || "Pen") + "[" + gex4[3] + "]");
    if (gex4[4] == "1")
        exText.push(homeTeam + " " + (_locModel.T.T_Ex_Win || "Win"));
    else if (gex4[4] == "2")
        exText.push(awayTeam + " " + (_locModel.T.T_Ex_Win || "Win"));
    return exText.join(", ");
}

function buildTags(tpl, data) {
    return tpl.replace(/>[\s\n\r]+</g, "><").replace(/\{\$(\w+)\}/g, function (a, b) {
        return (b in data) ? data[b] : "";
    });
}

function backOrClose() {

}

function cancelBubble(evt) {
    //cancel bubble
    var e = (evt) ? evt : window.event;
    if (!e) return;
    if (window.event) {
        e.cancelBubble = true;
    } else {
        e.stopPropagation();
    }
};

function seoVsTitle(hn, gn) {
    return (hn.toLowerCase().split(/([^<]+)[<]?/)[1] + " vs " + gn.toLowerCase()).replace(/[\W]+/g, '-');
}

function seoTitle(n) {
    return (n.toLowerCase()).replace(/[\[\]\s\(\)\'\.]+/g, '-')
}

function twitterShare(contentId) {

    var u = document.getElementsByClassName("share_url")[0].content;

    var t = document.getElementsByClassName("share_title")[0].content;
    popUtil.closePopup(1);
    window.open("https://twitter.com/share/?text=" + encodeURIComponent(t) + "&url=" + encodeUrlWithUnicode(u));
}

function encodeUrlWithUnicode(u) {
    var reg = /([\u0100-\uFFFF]+)/g;
    var sp = u.split(reg);
    var url = "";
    for (var i = 0; i < sp.length; i++) {
        if (reg.test(sp[i])) {
            url += encodeURIComponent(encodeURIComponent(sp[i]));
        }
        else {
            url += encodeURIComponent(sp[i]);
        }
    }

    return url;
}

function fbShare(contentId) {

    var u = document.getElementsByClassName("share_url")[0].content;

    var fb_u = document.getElementsByClassName("fb_share_url")[0];
    if (fb_u) {
        u = fb_u.content;
    }

    var t = document.getElementsByClassName("share_title")[0].content;
    var fb_t = document.getElementsByClassName("fb_share_title")[0];
    if (fb_u) {
        t = fb_t.content;
    }
    popUtil.closePopup(1);

    window.open("http://www.facebook.com/sharer.php?u=" + encodeURIComponent(u) + "&t=" + encodeURIComponent(t));
}
function oddsCompare(oval, val) {
    oval = oval ? oval.toString() : "";
    val = val ? val.toString() : "";
    //return 1,0,-1 for float odds, string, or 0/0.5
    var n_oval = parseFloat(oval), n_val = parseFloat(val);
    if (oval && oval.indexOf("/") != -1)
        n_oval = (oval.indexOf("-") == -1 ? 1 : -1) * (parseFloat(oval.replace("-","").split("/")[0]) + parseFloat(oval.replace("-").split("/")[1])) / 2;
    if (oval && oval.indexOf("/") != -1)
        n_val = (val.indexOf("-") == -1 ? 1 : -1) * (parseFloat(val.replace("-","").split("/")[0]) + parseFloat(val.replace("-").split("/")[1])) / 2;

    if (n_val > n_oval) return 1;
    else if (n_val == n_oval) return 0;
    return -1;
}

var oTool = {
    getPL: function (ot, hVal, pVal, gVal) {
        switch (ot) {
            case 1: case '1'://HK
                return [hVal, pVal, gVal];
                break;
            case 2: case '2'://Ind
                return [this.toIN(hVal), pVal, this.toIN(gVal)];
                break;
            case 3: case '3'://US
                return [this.toUS(hVal), pVal, this.toUS(gVal)];
                break;
            case 4: case '4'://Europe
                return [this.toEU(hVal), pVal, this.toEU(gVal)];
                break;
            case 5: case '5'://ML
                return [this.toML(hVal), pVal, this.toML(gVal)];
                break;
        }
    },
    toIN: function (val) {
        if (!val) return "";
        var fVal = parseFloat(val);
        if (isNaN(fVal)) return "";
        return (fVal < 1) ? (0 - 1 / fVal).toFixed(2) : val;
    },
    toML: function (val) {
        if (!val) return "";
        var fVal = parseFloat(val);
        if (isNaN(fVal)) return "";
        return (fVal > 1) ? (0 - 1 / fVal).toFixed(2) : val;
    },
    toEU: function (val) {
        if (!val) return "";
        var fVal = parseFloat(val);
        if (isNaN(fVal)) return "";
        return (fVal + 1).toFixed(2);
    },
    toUS: function (val) {
        if (!val) return "";
        var fVal = parseFloat(val);
        if (isNaN(fVal)) return "";
        if (fVal < 1)
            return Math.round(0 - 100 * ((1 / fVal).toFixed(2)));
        else
            return Math.round(100 * fVal);
    }
};

function closeBack(opt) {

    var defUrl = "/";

    if (opt == 2 || location.href.indexOf("basketball") != -1) {
        defUrl = "/basketball/";
    }
    else if (opt == 3) {
        defUrl = "/football/leagues-cups/";
    }
    else if (opt == 4 || location.href.indexOf("/article/") != -1) {
        defUrl = "/news/";
    }

    if (!document.referrer || document.referrer.indexOf(location.host) == -1) {
        // back to home when open by another domain
        location.href = defUrl;
        return;
    }

    if (bomHelper.firefox || bomHelper.chrome) {
        close();
        history.back();
    }
    else if (bomHelper.android.version || bomHelper.uc) {
        // for win.open
        history.back();

        window.opener = null;
        window.open("", "_self", "").close();
    }
    else {
        window.opener = null;
        window.open("", "_self", "").close();
        if (document.referrer) {
            history.back();
        }
    }
}

function toSetting(opt) {
    if (typeof (localStorage) != "undefined") {
        localStorage.setItem("pPSet", window.location.href);
    }
    else {
        writeCookie("pPSet", window.location.href);
    }

    var param = opt ? 1 : 0;
    if (window._ScoreOnly) {
        param = 2;
    }

    window.location.href = "/about/setting/" + (param ? "?s=" + param : "");
}

function Hashtable() {
    this._hash = new Object();
    this.add = function (key, value) {
        if (typeof (key) != "undefined") {
            this._hash[key] = typeof (value) == "undefined" ? null : value;
            return true;
        }
        else
            return false;
    }
    this.remove = function (key) { delete this._hash[key]; }
    this.keys = function () {
        var keys = new Array();
        for (var key in this._hash) {
            keys.push(key);
        }
        return keys;
    }
    this.count = function () { var i = 0; for (var k in this._hash) { i++; } return i; }
    this.items = function (key) { return this._hash[key]; }
    this.contains = function (key) {
        return typeof (this._hash[key]) != "undefined";
    }
    this.clear = function () { for (var k in this._hash) { delete this._hash[k]; } }
}

function toLink(o) {
    cancelBubble();
    goTo(o.getAttribute("data-link"));
}

function goTo(url, id) {
    var newA = document.createElement("a");
    newA.id = id || ("tmp_" + new Date().getTime());
    newA.target = '_blank';
    newA.href = url;
    document.body.appendChild(newA);
    newA.click();
    document.body.removeChild(newA);
}