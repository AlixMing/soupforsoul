
var sendtime_num = 0;
var sendtime_t = 0;
var loading_id = -1;

function init() {

    $("#txtPhone").blur(function() {
        var strPhone = $(this).val();
        if (!checkPhone(strPhone)) {
            return;
        }

        $.ajax({
            type: "POST",
            url: "?s=Activity/Liumi/checkisreg",
            dataType: "json",
            data: {"phone":strPhone},
            success: function(data) {
                if (data.status == 1) {
                    $('#regTips').hide();
                }
                else {
                    showTips("此手机号已被注册");
                    $('#regTips').show();
                }
            }
        });
    });
}

function doRegister() {

    var strPhone = $("#txtPhone").val();
    var strPwd = $("#txtPwd").val();
    var strCode = $("#txtCode").val();
    if (!checkPhone(strPhone)) {
        showTips("请输入正确的手机号码");
        return;
    }
    if (strPwd.length < 6) {
        showTips("请输入六位以上的密码");
        return;
    }
    if (isNull(strCode)) {
        showTips("请输入验证码");
        return;
    }
    if (!$("#chk-rule").is(':checked')) {
        showTips("请勾选 我已阅读并同意相关服务条款");
        return;
    }

    var param = {};
    param.phone = strPhone;
    param.code = strCode;
    param.password = strPwd;
    $.ajax({
        type: "POST",
        url: "?s=Activity/Liumi/register",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.status == 1) {
                if (data.hasOwnProperty("url")) {
                    location.href = data.url;
                } else if (data.hasOwnProperty("msg")) {
                    showTips(data.msg);
                } else {
                    showTips("注册成功");
                }
            }
            else {
                showTips(data.message);
            }
        },
        beforeSend: function(XMLHttpRequest){
            showLoading();
        },
        complete: function(XMLHttpRequest, textStatus){
            hideLoading();
        }
    });
}

function getSmsCode() {

    if (sendtime_num > 0) return;

    var strPhone = $("#txtPhone").val();
    if (!checkPhone(strPhone)) {
        showTips("请输入正确的手机号码");
        return;
    }

    var param = {};
    param.phone = strPhone;
    $.ajax({
        type: "POST",
        url: "?s=Activity/Liumi/getSmsCode",
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

                $("#txtCode").val(data.message);
            }
            else {
                showTips(data.message);
            }
        }
    });
}

function sendtime() {
    sendtime_num--;
    $btnCode = $('#btnCode');
    $btnCode.val("重新发送("+sendtime_num+")");
    if (sendtime_num == 0) {
        $btnCode.val("重新发送");
        clearTimeout(sendtime_t);
    }
}

function showTips(msg) {
    layer.open({
        content: msg
        ,skin: 'msg'
        ,time: 2 //2秒后自动关闭
    });
}

function showLoading() {
    if (loading_id != -1) return;
    loading_id = layer.open({
        type: 2
        ,content: '海控君正处理中...'
        ,shadeClose: false
    });
}

function hideLoading() {
    layer.close(loading_id);
    loading_id = -1;
}