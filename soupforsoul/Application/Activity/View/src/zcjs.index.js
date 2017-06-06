
var sendtime_num = 0;
var sendtime_t = 0;
var m_cache_key = "zcjs";

function init() {
    var savedata = window.localStorage.getItem(m_cache_key);
    if (savedata) {
        try {
            var d = JSON.parse(savedata);
            var today = formatDate(null, '-');
            if (today == d.date) {
                getCode(d.phone, false);
            }
        }
        catch (e) {

        }
    }
}

function doRegister() {

    var strPhone = $("#txtRPhone").val();
    var strPwd = $("#txtRPwd").val();
    var strCode = $("#txtRCode").val();
    if (!checkPhone(strPhone)) {
        alert("请输入正确的电话号码");
        return;
    }
    if (strPwd.length < 6) {
        alert("请输入六位以上的密码");
        return;
    }
    if (isNull(strCode)) {
        alert("请输入验证码");
        return;
    }

    var param = {};
    param.phone = strPhone;
    param.code = strCode;
    param.password = strPwd;
    $.ajax({
        type: "POST",
        url: "?s=Activity/zcjs/register",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.status == 1) {
                addCode(data.username);
            }
            else {
                alert(data.message);
            }
        }
    });
}

function getSmsCode() {

    if (sendtime_num > 0) return;

    var strPhone = $("#txtRPhone").val();
    if (!checkPhone(strPhone)) {
        alert("请输入正确的电话号码");
        return;
    }

    var param = {};
    param.phone = strPhone;
    $.ajax({
        type: "POST",
        url: "?s=Activity/Zcjs/getSmsCode",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.status == 1) {

                sendtime_num = 60;
                sendtime_t = setInterval('sendtime()', 1000);
            }
            else if (data.status == -1) {

                sendtime_num = 60;
                sendtime_t = setInterval('sendtime()', 1000);

                $("#txtRCode").val(data.message);
            }
            else {
                alert(data.message);
            }
        }
    });
}

function doLogin() {

    var strPhone = $("#txtLPhone").val();
    var strPwd = $("#txtLPwd").val();
    if (!checkPhone(strPhone)) {
        alert("请输入正确的电话号码");
        return;
    }
    if (strPwd.length < 6) {
        alert("请输入六位以上的密码");
        return;
    }

    var param = {};
    param.phone = strPhone;
    param.password = strPwd;
    $.ajax({
        type: "POST",
        url: "?s=Activity/zcjs/login",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.status == 1) {
                getCode(data.username, true);
            }
            else {
                alert(data.info);
            }
        }
    });
}

function addCode(phone) {
    var param = {};
    param.phone = phone;
    param.bid = m_bid;
    $.ajax({
        type: "POST",
        url: "?s=Activity/Zcjs/addCode",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.errorcode == 0) {
                saveCache(phone);
                location.href = "?s=Activity/Zcjs/code/phone/" + phone;
            }
            else {
                alert(data.message);
            }
        }
    });
}

function getCode(phone, isLogin) {
    var param = {};
    param.phone = phone;
    param.bid = m_bid;
    $.ajax({
        type: "POST",
        url: "?s=Activity/Zcjs/getCode",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.errorcode == 0) {
                if (isLogin) saveCache(phone);
                location.href = "?s=Activity/Zcjs/code/phone/" + phone;
            }
            else {
                if (isLogin) alert('本次活动仅限新注册用户参与，谢谢您的关注！');
                window.localStorage.removeItem(m_cache_key);
            }
        }
    });
}

function saveCache(phone) {
    var savedata = {};
    savedata.phone = phone;
    savedata.date = formatDate(null, '-');
    window.localStorage.setItem(m_cache_key, JSON.stringify(savedata));
}

function sendtime() {
    sendtime_num--;
    $btnCode = $('#btnCode');
    $btnCode.html(sendtime_num + "秒后重新发送");
    if (sendtime_num == 0) {
        $btnCode.html("重新发送");
        clearTimeout(sendtime_t);
    }
}

function showLogin() {
    $("#divRegister").hide();
    $("#divLogin").show();
}

function showRegister() {
    $("#divRegister").show();
    $("#divLogin").hide();
}