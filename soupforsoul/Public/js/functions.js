

function getMonthDiff(startDate, endDate) {
    var m1 = parseInt(startDate.split("-")[1].replace(/^0+/, "")) + parseInt(startDate.split("-")[0]) * 12;
    var m2 = parseInt(endDate.split("-")[1].replace(/^0+/, "")) + parseInt(endDate.split("-")[0]) * 12;
    return Math.abs(m2 - m1);
}

function getDateDiff(startDate, endDate) {
    var startTime = parseDate(startDate).getTime();
    var endTime = parseDate(endDate).getTime();
    var dates = Math.abs((startTime - endTime)) / (1000 * 60 * 60 * 24);
    return Math.ceil(dates);
}

function getCurDiff(endDate) {
    var startTime = new Date().getTime();
    var endTime = parseDate(endDate).getTime();
    var dates = Math.abs((startTime - endTime)) / (1000 * 60 * 60 * 24);
    return Math.ceil(dates);
}

function formatDate(d, c) {
    var date = parseDate(d);
    var strM = (date.getMonth() + 1) < 10 ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1);
    var strD = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
    return date.getFullYear() + c + strM + c + strD;
}

function parseDate(input, format) {
    if (input) {
        format = format || 'YYYY-MM-DD hh:mm:ss';
        var parts = input.match(/(\d+)/g);
        while (parts.length < 6) parts.push(0);
        var i = 0;
        var fmt = {};
        // extract date-part indexes from the format
        format.replace(/(YYYY|MM|DD|hh|mm|ss)/g, function (part) {
            fmt[part] = i++;
        });

        return new Date(parts[fmt['YYYY']], parts[fmt['MM']] - 1, parts[fmt['DD']], parts[fmt['hh']], parts[fmt['mm']], parts[fmt['ss']]);
    }
    else {
        return new Date();
    }
}

function formatMilli(s) {//添加千位符
    var num = parseFloat(s).toString();
    if (/[^0-9\.]/.test(num)) return s;
    s = s.replace(/^(\d*)$/, "$1.");
    s = (s + "00").replace(/(\d*\.\d\d)\d*/, "$1");
    s = s.replace(".", ",");
    var re = /(\d)(\d{3},)/;
    while (re.test(s)) {
        s = s.replace(re, "$1,$2");
    }
    s = s.replace(/,(\d\d)$/, ".$1");
    return s.replace(/^\./, "0.")
}

function isNull(str) {//判断空格
    if (str == "") return true;
    return /^[ ]+$/.test(str);
}

function checkPhone(strPhone) {
    //return strPhone.match(/^((13[0-9])|(15[^4,\D])|(18[0-9])|(17[6-8])|(14[5, 7]))\d{8}$/);
    return strPhone.match(/^1[34578]\d{9}$/);
}

function checkNum(strVal) {
    return strVal.match(/^[0-9]+([.]{1}[0-9]+){0,1}$/);
}

function checkInt(strVal) {
    return strVal.match(/^[0-9]*[1-9][0-9]*$/);
}

function isWeiXin() {
    var ua = window.navigator.userAgent.toLowerCase();
    return (ua.match(/MicroMessenger/i) == 'micromessenger');
}

function isIOS() {
    var ua = window.navigator.userAgent.toLowerCase();
    return /iphone|ipad|ipod/.test(ua);
}

function isAndroid() {
    var ua = window.navigator.userAgent.toLowerCase();
    return /android/.test(ua);
}

function trim(str) { //删除左右两端的空格
    return str.replace(/(^\s*)|(\s*$)/g, "");
}

function ltrim(str) { //删除左边的空格
    return str.replace(/(^\s*)/g, "");
}

function rtrim(str) { //删除右边的空格
    return str.replace(/(\s*$)/g, "");
}

function gotoUrl(url) {
    location.href = url;
}