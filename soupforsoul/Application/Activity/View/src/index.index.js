/**
 * Created by ljp on 2016/7/18.
 */

var m_code_t = 0;
var m_code_tid = 0;
var m_signList = [];
var m_mySign = [];
var m_curSort = 1;
var m_signing = false;
var m_signCnt = 0;
var m_prizeList = [];

function init() {

    checkLogin();

    G_weixin.addEventListener(WEIXIN_CACHE_SUCCESS, function (data) {
        $("#wxheadimg").attr("src", data.headimgurl);
        $("#wxnickname").text(data.nickname);
        loadConfig();
        loadPrize();
        G_weixin.bindShare();
    });

    G_weixin.addEventListener(WEIXIN_CACHE_FAIL, function (data) {
        G_weixin.checkCode();
    });

    G_weixin.addEventListener(WEIXIN_LOGIN_COMPLETE, function (data) {
        $("#wxheadimg").attr("src", data.headimgurl);
        $("#wxnickname").text(data.nickname);

        saveWxUser(data.openid, data.nickname, data.headimgurl);
        loadConfig();
        loadPrize();
        G_weixin.bindShare();
    });

    G_weixin.loadCache();
}

function saveWxUser(openid, nickname, headimgurl) {
    var param = {};
    param.openid = openid;
    param.nickname = nickname;
    param.headimgurl = headimgurl;
    $.ajax({
        type: "POST",
        url: "?s=Aoyun/Index/saveWxUser",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.errorcode == 0) {
                G_weixin.saveCache(data);
            }
        }
    });
}

function loadConfig() {
    var param = {};
    $.ajax({
        type: "POST",
        url: "?s=Aoyun/Index/getSignConfig",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.errorcode == 0) {
                m_signList = data.list;
                m_curSort = data.curSort;
                getMySign();
            }
        }
    });
}

function getMySign() {

    var param = {};
    param.openid = G_weixin.getOpenid();
    $.ajax({
        type: "POST",
        url: "?s=Aoyun/Index/getMySign",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.errorcode == 0) {
                m_mySign = data.list;
                m_signCnt = data.cnt;
                $("#fireCnt").text(m_signCnt);
            }
        },
        complete: function () {
            bindSign();
            enableSign();
        }
    });
}

function enableSign() {
    if (checkSign(m_curSort))
        $("#txtSign").show();
    else
        $("#btnSign").show();
}

function checkSign(s) {
    for (var i = 0; i < m_mySign.length; i++) {
        if (m_mySign[i].sort == s) {
            return true;
        }
    }
    return false;
}

function bindSign() {
    var temp = '';
    for (var i = 0; i < m_signList.length; i+=6) {
        temp += '<div class="row">';
        for (var j = 0; j < 6; j++) {
            var idx = i + j;

            if (idx < m_signList.length) {
                var icon = checkSign(m_signList[idx].sort) ? m_signList[idx].icon2 : m_signList[idx].icon;
                if (m_signList[idx].sort == m_curSort)
                    temp += '<div class="item"><img class="cur" src="'+icon+'"></div>';
                else
                    temp += '<div class="item"><img class="normal" src="'+icon+'"></div>';
            }
            else {
                temp += '<div class="item"><img class="normal" src="'+VIEW_PATH+'/image/fire-non-icon.png"></div>';
            }
        }
        temp += '</div>';
    }

    $("#sign-bd").html(temp);
}

function doSign() {
    if (!G_login_flag && m_signCnt >= 5) {
        showLoginDialog();
        return;
    }

    if (!m_signing) {
        m_signing = true;
        var param = {};
        param.openid = G_weixin.getOpenid();
        $.ajax({
            type: "POST",
            url: "?s=Aoyun/Index/sign",
            data: param,
            dataType: "json",
            success: function (data) {
                if (data.errorcode == 0) {

                    m_mySign.push({sort: m_curSort});
                    m_signCnt++;
                    $("#fireCnt").text(m_signCnt);
                    bindSign();
                    $("#txtSign").show();
                    $("#btnSign").hide();
                }
                else {
                    alert(data.message);
                }
            },
            complete: function () {
                m_signing = false;
            }
        });
    }
}

function loadPrize() {
    var param = {};
    $.ajax({
        type: "POST",
        url: "?s=Aoyun/Index/getPrize",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.errorcode == 0) {
                m_prizeList = data.list;

                var temp = '';
                for (var i = 0; i < m_prizeList.length; i++) {
                    temp += '<div class="swiper-slide">';
                    temp += '<img src="'+m_prizeList[i].prizeimg+'">';
                    temp += '<div class="title">签到'+m_prizeList[i].prizestatus+'天</div>';
                    temp += '<div class="desc">'+m_prizeList[i].prizename+'</div>';
                    temp += '</div>';
                }
                
                $('.swiper-wrapper').html(temp);
                var swiper = new Swiper('.swiper-container', {
                    pagination: '.swiper-pagination',
                    nextButton: '.swiper-button-next',
                    prevButton: '.swiper-button-prev',
                    slidesPerView: 3,
                    spaceBetween: 20,
                    loop : true,
                    autoplay : 3000
                });
            }
        }
    });
}

function showShareTips() {
    $("#shareTips").show();
}

function hideShareTips() {
    $("#shareTips").hide();
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
    param.openid = G_weixin.getOpenid();
    $.ajax({
        type: "POST",
        url: "?s=Aoyun/Index/register",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.status == 1) {
                savePhone(strPhone);
            }
            else {
                alert(data.message);
            }
        }
    });
}

function getCode() {

    if (m_code_t > 0) return;

    var strPhone = $("#txtRPhone").val();
    if (!checkPhone(strPhone)) {
        alert("请输入正确的电话号码");
        return;
    }
    
    var param = {};
    param.phone = strPhone;
    $.ajax({
        type: "POST",
        url: "?s=Aoyun/Index/getSmsCode",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.status == 1) {

                m_code_t = 60;
                m_code_tid = setInterval('onCodeTick()', 1000);
            }
            else if (data.status == -1) {

                m_code_t = 60;
                m_code_tid = setInterval('onCodeTick()', 1000);

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
    param.openid = G_weixin.getOpenid();
    $.ajax({
        type: "POST",
        url: "?s=Aoyun/Index/login",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.status == 1) {
                savePhone(strPhone);
                hideLoginDialog();
            }
            else {
                alert(data.message);
            }
        }
    });
}

function onCodeTick() {
    m_code_t--;
    $btnCode = $('#btnCode');
    $btnCode.html(m_code_t + "秒后重新发送");
    if (m_code_t == 0) {
        $btnCode.html("重新发送");
        clearTimeout(m_code_tid);
    }
}

function showLogin() {
    $("#divRegister").hide();
    $("#divLogin").show();
    $("#divCode").hide();
}

function showRegister() {
    $("#divRegister").show();
    $("#divLogin").hide();
    $("#divCode").hide();
}

function showLoginDialog() {
    $("#loginDlg").css({"opacity":"0","display":"block"}).animate({opacity:"1"},500);
    $("#divMask").show();
}

function hideLoginDialog() {
    $("#loginDlg").css("display","none");
    $("#divMask").hide();
}

function showRule() {
    $("#dialogTitle").attr("src", VIEW_PATH + "/image/rule-title.png");

    var temp = '<img src="'+VIEW_PATH+'/image/rule-info2.png"/>';

    $("#dialogContent").html(temp);
    $("#divDialog").show();
}

function showMyPrize() {
    $("#dialogTitle").attr("src", VIEW_PATH + "/image/prize-title.png");
    $("#dialogContent").html("");
    $("#divDialog").show();
}

function hideDialog() {
    $("#divDialog").hide();
}