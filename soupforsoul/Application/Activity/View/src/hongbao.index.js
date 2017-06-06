
var sendtime_num = 0;
var sendtime_t = 0;
var loading_id = -1;
var can_reg = false;

function init() {
    $("#regPhone").blur(function() {
        var strPhone = $(this).val();
        if (!checkPhone(strPhone)) {
            return;
        }

        $.ajax({
            type: "POST",
            url: "?s=Activity/Hongbao/ckphone",
            dataType: "json",
            data: {"phone":strPhone},
            success: function(data) {
                if (data.status == 1) {
                    $("#hide-area").show();
                    can_reg = true;
                }
                else {
                    showLogin(strPhone);
                }
            }
        });
    });
}

function doRegister() {
    if (!can_reg) return;
    var strPhone = $("#regPhone").val();
    var strCode = $("#regCode").val();
    if (!checkPhone(strPhone)) {
        showTips("请输入正确的手机号码");
        return;
    }
    if (isNull(strCode)) {
        showTips("请输入验证码");
        return;
    }

    var param = {};
    param.phone = strPhone;
    param.code = strCode;
    param.uid = UID;
    $.ajax({
        type: "POST",
        url: "?s=Activity/Hongbao/register",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.status == 1 || data.status == -1) {
                showRegisterResult(strPhone);
                bindShare(data.uid);
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

function doLogin() {
    var strPhone = $("#loginPhone").val();
    var strPwd = $("#loginPwd").val();
    if (!checkPhone(strPhone)) {
        showTips("请输入正确的手机号码");
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
        url: "?s=Activity/Hongbao/login",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.status == 1) {
                hideLogin();
                showLoginResult(strPhone);
                bindShare(data.uid);
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

    var strPhone = $("#regPhone").val();
    if (!checkPhone(strPhone)) {
        showTips("请输入正确的手机号码");
        return;
    }

    var param = {};
    param.phone = strPhone;
    $.ajax({
        type: "POST",
        url: "?s=Activity/Hongbao/getSmsCode",
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

                $("#regCode").val(data.message);
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

function showRegisterResult(phone) {
    var temp = '';
    temp += '<div class="top">';
    temp += '<img class="w-full" src="'+VIEW_PATH+'/image/hongbao/hongbao-top.png">';
    temp += '<div class="info">';
    temp += '<div class="tips">　领取成功！</div>';
    temp += '<div class="money"><span>20</span>元</div>';
    temp += '</div>';
    temp += '</div>';
    temp += '<div class="mid">';
    temp += '<div class="info" style="padding-bottom: 15px;">';
    temp += '<div class="tips"><span>20元</span>红包已到您的账户！</div>';
    temp += '<div class="phone">'+phone.substr(0,3)+'****'+phone.substr(7)+'</div>';
    temp += '<div class="tips">更多福利尽在海控金融！</div>';
    temp += '</div>';
    temp += '</div>';
    temp += '<div class="bottom">';
    temp += '<img class="w-full" src="'+VIEW_PATH+'/image/hongbao/hongbao-bottom.png">';
    temp += '</div>';
    temp += '<div class="button">';
    temp += '<a href="https://www.haikongjinrong.com/Appservice/member/coupon"><img class="w-full" src="'+VIEW_PATH+'/image/hongbao/btn-my.png"></a>';
    temp += '</div>';
    temp += '<div class="button">';
    temp += '<a href="javascript:$(\'.share-tips\').show()"><img class="w-full" src="'+VIEW_PATH+'/image/hongbao/btn-share.png"></a>';
    temp += '</div>';
    $('#register-area').html(temp);
}

function showLoginResult(phone) {
    var temp = '';
    temp += '<div class="top">';
    temp += '<img class="w-full" src="'+VIEW_PATH+'/image/hongbao/hongbao-top.png">';
    //temp += '<img class="star" src="'+VIEW_PATH+'/image/hongbao/pic-star.png">';
    //temp += '<a href="javascript:hideLogin()"><img class="close" src="'+VIEW_PATH+'/image/hongbao/btn-close.png"></a>';
    temp += '<div class="info">';
    temp += '<div class="money">　登录成功！</div>';
    temp += '<div class="tips" style="padding: 10px 0">亲爱的'+phone.substr(0,3)+'****'+phone.substr(7)+'</div>';
    temp += '</div>';
    temp += '</div>';
    temp += '<div class="mid">';
    temp += '<div class="info" style="padding-bottom: 15px;">';
    temp += '<div class="tips">';
    temp += ' 分享链接给好友<br>';
    temp += '成功推荐就可得红包哦~<br>';
    temp += '更多福利，尽在海控金融！';
    temp += '</div>';
    temp += '</div>';
    temp += '</div>';
    temp += '<div class="bottom">';
    temp += '<img class="w-full" src="'+VIEW_PATH+'/image/hongbao/hongbao-bottom.png">';
    temp += '</div>';
    temp += '<div class="button">';
    temp += '<a href="javascript:$(\'.share-tips\').show()"><img class="w-full" src="'+VIEW_PATH+'/image/hongbao/btn-share2.png"></a>';
    temp += '</div>';
    $('#register-area').html(temp);
}

function showLogin(phone) {
    var temp = '';
    temp += '<div class="hongbao-area" id="login-area">';
    temp += '<div class="top">';
    temp += '<img class="w-full" src="'+VIEW_PATH+'/image/hongbao/hongbao-top.png">';
    temp += '<img class="star" src="'+VIEW_PATH+'/image/hongbao/pic-star.png">';
    temp += '<a href="javascript:hideLogin()"><img class="close" src="'+VIEW_PATH+'/image/hongbao/btn-close.png"></a>';
    temp += '<div class="info">';
    temp += '<div class="tips">';
    temp += '亲~本活动仅限新用户参与哦<br>';
    temp += '不要紧，马上登陆<br>';
    temp += '分享链接给新朋友<br>';
    temp += '大家一起得红包！';
    temp += '</div>';
    temp += '</div>';
    temp += '</div>';
    temp += '<div class="mid">';
    temp += '<div class="form-area">';
    temp += '<div class="row row-first">';
    temp += '<div class="text">';
    temp += '<div class="icon"><img src="'+VIEW_PATH+'/image/user-icon.png"></div>';
    temp += '<div class="input"><input id="loginPhone" value="'+phone+'" type="text" maxlength="18" placeholder="请输入手机号"></div>';
    temp += '</div>';
    temp += '</div>';
    temp += '<div class="row">';
    temp += '<div class="text">';
    temp += '<div class="icon"><img src="'+VIEW_PATH+'/image/lock-icon.png"></div>';
    temp += '<div class="input"><input id="loginPwd" type="password" maxlength="20" placeholder="请输入密码"></div>';
    temp += '</div>';
    temp += '</div>';
    temp += '</div>';
    temp += '</div>';
    temp += '<div class="bottom">';
    temp += '<img class="w-full" src="'+VIEW_PATH+'/image/hongbao/hongbao-bottom.png">';
    temp += '</div>';
    temp += '<div class="button">';
    temp += '<a href="javascript:doLogin()"><img class="w-full" src="'+VIEW_PATH+'/image/hongbao/btn-login.png"></a>';
    temp += '</div>';
    temp += '</div>';
    $(".mask-area").html(temp).css({"opacity":"0","display":"block"}).animate({opacity:"1"},500);
}

function hideLogin() {
    $('#login-area').html('');
    $(".mask-area").hide();
}

function bindShare(uid) {
    if (window.weixin) {
        window.weixin.share({
            SHARE_URL:G_share_url+"/uid/"+uid
        });
    }
}