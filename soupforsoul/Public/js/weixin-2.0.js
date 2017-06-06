/**
 * Created by ljp on 2015/9/29.
 */

var WEIXIN_PAY_SUCCESS = "paysuccess";
var WEIXIN_PAY_CANCEL = "paycancel";
var WEIXIN_SHARE_SUCCESS = "sharesuccess";
var WEIXIN_SHARE_CANCEL = "sharecancel";
var WEIXIN_USERINFO_SUCCESS = "userinfosuccess";
var WEIXIN_USERINFO_FAIL = "userinfofail";
var WEIXIN_TYPE_ALL = 0;
var WEIXIN_TYPE_ONLYSHARE = 1;
var WEIXIN_TYPE_ONLYUSERINFO = 2;

function wxrandomString(len) {
    len = len || 32;
    var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
    var maxPos = $chars.length;
    var pwd = '';
    for (i = 0; i < len; i++) {
        pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}

function Weixin(options) {

    var _events = {};
    var _options = options || {
        APPID:"",
        APPSECRET:"",
        DEBUG:false,
        CACHE:true,
        PREFIX:"",
        TYPE:WEIXIN_TYPE_ALL,

        APP_TOKEN_PATH:"",
        WEB_TOKEN_PATH:"",
        APP_USERINFO_PATH:"",
        WEB_USERINFO_PATH:"",
        GETTICKET_PATH:"",

        SHARE_TITLE:"分享标题",
        SHARE_DESC:"分享描述",
        SHARE_URL:window.location.href,
        SHARE_ICON:"",

        PAY_PATH:"",

        CODE:"",
        CODE_PATH:"",
        CODE_STATE:"123"
    };

    var _wxData = {};
    var _app_access_token = "";
    var _web_access_token = "";
    var _openid = "";

    function getAppAccessToken(flag) {
        var param = {};
        $.ajax({
            type: "GET",
            url: _options.APP_TOKEN_PATH,
            data: param,
            dataType: "json",
            success: function (data) {
                if (data.hasOwnProperty("errcode")) {
                    //alert(msg.errcode);
                }
                else {
                    _app_access_token = data.access_token;
                    if (_options.TYPE == WEIXIN_TYPE_ALL) {
                        if (flag) getAppUserInfo();
                        getTicket();
                    }
                    else if (_options.TYPE == WEIXIN_TYPE_ONLYSHARE) {
                        getTicket();
                    }
                }
            }
        });
    }

    function getWebAccessToken(code) {
        var param = {};
        param.code = code;
        $.ajax({
            type: "GET",
            url: _options.WEB_TOKEN_PATH,
            data: param,
            dataType: "json",
            success: function (data) {
                if (data.hasOwnProperty("errcode")) {
                    //alert(msg.errcode);
                }
                else {
                    _openid = data.openid;
                    _web_access_token = data.access_token;
                    if (_options.TYPE == WEIXIN_TYPE_ALL) {
                        getAppUserInfo();
                    }
                    else if (_options.TYPE == WEIXIN_TYPE_ONLYUSERINFO) {
                        getWebUserInfo();
                    }
                }
            }
        });
    }

    function getAppUserInfo() {
        if (_app_access_token != "" && _openid != "") {
            var param = {};
            param.access_token = _app_access_token;
            param.openid = _openid;
            $.ajax({
                type: "GET",
                url: _options.APP_USERINFO_PATH,
                data: param,
                dataType: "json",
                success: function (data) {
                    if (data.hasOwnProperty("errcode")) {
                        handle(WEIXIN_USERINFO_FAIL, data);
                    }
                    else {
                        _wxData = data;
                        saveCache(data);
                        handle(WEIXIN_USERINFO_SUCCESS, data);
                    }
                }
            });
        }
    }

    function getWebUserInfo() {
        if (_web_access_token != "" && _openid != "") {
            var param = {};
            param.access_token = _web_access_token;
            param.openid = _openid;
            $.ajax({
                type: "GET",
                url: _options.WEB_USERINFO_PATH,
                data: param,
                dataType: "json",
                success: function (data) {
                    if (data.hasOwnProperty("errcode")) {
                        handle(WEIXIN_USERINFO_FAIL, data);
                    }
                    else {
                        _wxData = data;
                        saveCache(data);
                        handle(WEIXIN_USERINFO_SUCCESS, data);
                    }
                }
            });
        }
    }

    function getTicket() {
        var param = {};
        param.access_token = _app_access_token;
        $.ajax({
            type: "GET",
            url: _options.GETTICKET_PATH,
            data: param,
            dataType: "json",
            success: function (data) {
                if (data.hasOwnProperty("errcode") && data.errcode != 0) {
                    //alert(msg.errcode);
                }
                else {
                    var noncestr = wxrandomString(16);
                    var timestamp = Math.round(new Date().getTime() / 1000);
                    var str = "jsapi_ticket=" + data.ticket + "&noncestr=" + noncestr + "&timestamp=" + timestamp + "&url=" + window.location.href;
                    var signature = hex_sha1(str);
                    wx.config({
                        debug: _options.DEBUG,
                        appId: _options.APPID,
                        timestamp: timestamp,
                        nonceStr: noncestr,
                        signature: signature,
                        jsApiList: [
                            'onMenuShareAppMessage',
                            'onMenuShareTimeline',
                            'hideMenuItems',
                            'chooseWXPay',
                            'checkJsApi'
                        ]
                    });
                    wx.ready(function () {
                        wx.checkJsApi({
                            jsApiList: [
                                'onMenuShareAppMessage',
                                'onMenuShareTimeline',
                                'hideMenuItems',
                                'chooseWXPay'
                            ]
                        });

                        initShare(_options);

                        wx.hideMenuItems({
                            menuList: [
                                "menuItem:share:qq",
                                "menuItem:share:weiboApp",
                                "menuItem:share:facebook",
                                "menuItem:share:QZone",
                                "menuItem:copyUrl",
                                "menuItem:originPage",
                                "menuItem:openWithQQBrowser",
                                "menuItem:openWithSafari",
                                "menuItem:share:email",
                                "menuItem:readMode"
                            ] // 要隐藏的菜单项，只能隐藏“传播类”和“保护类”按钮，所有menu项见附录3
                        });
                    });
                }
            }
        });
    }

    function share(shareData) {
        if (!shareData.hasOwnProperty("SHARE_TITLE")) { shareData.SHARE_TITLE = _options.SHARE_TITLE; }
        if (!shareData.hasOwnProperty("SHARE_DESC")) { shareData.SHARE_DESC = _options.SHARE_DESC; }
        if (!shareData.hasOwnProperty("SHARE_URL")) { shareData.SHARE_URL = _options.SHARE_URL; }
        if (!shareData.hasOwnProperty("SHARE_ICON")) { shareData.SHARE_ICON = _options.SHARE_ICON; }
        initShare(shareData);
    }

    function initShare(shareData) {
        wx.onMenuShareAppMessage({
            title: shareData.SHARE_TITLE, // 分享标题
            desc: shareData.SHARE_DESC, // 分享描述
            link: shareData.SHARE_URL + "&openid=" + _openid, // 分享链接
            imgUrl: shareData.SHARE_ICON, // 分享图标
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                handle(WEIXIN_SHARE_SUCCESS, {});
            },
            cancel: function () {
                handle(WEIXIN_SHARE_CANCEL, {});
            }
        });
        wx.onMenuShareTimeline({
            title: shareData.SHARE_TITLE, // 分享标题
            link: shareData.SHARE_URL + "&openid=" + _openid, // 分享链接
            imgUrl: shareData.SHARE_ICON, // 分享图标
            success: function () {
                handle(WEIXIN_SHARE_SUCCESS, {});
            },
            cancel: function () {
                handle(WEIXIN_SHARE_CANCEL, {});
            }
        });
    }

    function pay(money, name, param) {
        param.total_fee = money;
        param.attach = name;
        param.openid = _openid;
        $.ajax({
            type: "POST",
            url: _options.PAY_PATH,
            data: param,
            dataType: "json",
            success: function (data) {
                if (data.hasOwnProperty("errcode")) {
                    //alert(msg.errcode);
                }
                else {
                    wx.chooseWXPay({
                        timestamp: data.timeStamp,
                        nonceStr: data.nonceStr,
                        package: data.package,
                        signType: data.signType, // 注意：新版支付接口使用 MD5 加密
                        paySign: data.paySign,
                        success: function (res) {
                            param.out_trade_no = data.out_trade_no;
                            handle(WEIXIN_PAY_SUCCESS, param);
                        },
                        cancel: function (res) {
                            handle(WEIXIN_PAY_CANCEL, param);
                        }
                    });
                }
            }
        });
    }

    function ready() {
        if (_options.TYPE == WEIXIN_TYPE_ONLYSHARE) {
            getAppAccessToken(true);
        }
        else {
            var pass = checkCache();
            if (pass) getAppAccessToken(false);
            else checkCode();
        }
    }

    function redirect() {
        var scope = "snsapi_base";
        if (_options.TYPE == WEIXIN_TYPE_ONLYUSERINFO) scope = "snsapi_userinfo";
        location.href = "https://open.weixin.qq.com/connect/oauth2/authorize?appid="+_options.APPID+"&redirect_uri="+encodeURI(_options.CODE_PATH)+"&response_type=code&scope="+scope+"&state="+_options.CODE_STATE+"#wechat_redirect";
    }

    function checkCode() {
        if (_options.CODE == "") {
            redirect();
            return false;
        }
        else {
            if (_options.TYPE == WEIXIN_TYPE_ONLYUSERINFO) {
                getWebAccessToken(_options.CODE);
            }
            else {
                getAppAccessToken(true);
                getWebAccessToken(_options.CODE);
            }
            return true;
        }
    }

    function checkCache() {
        var hasData = false;
        if (_options.CACHE) {
            var wx = localStorage.getItem(_options.PREFIX + "_weixin");
            if (wx) {
                try {
                    _wxData = JSON.parse(wx);
                    _openid = _wxData.openid;
                    _app_access_token = _wxData.app_access_token;
                    _web_access_token = _wxData.web_access_token;
                    hasData = true;
                }
                catch (e) {
                    _wxData = {};
                }
            }
        }
        return hasData;
    }

    function saveCache(data) {
        data.openid = _openid;
        data.app_access_token = _app_access_token;
        data.web_access_token = _web_access_token;
        var json = JSON.stringify(data);
        localStorage.setItem(_options.PREFIX + "_weixin", json);
    }

    function handle(eventName, data) {
        _events[eventName] && _events[eventName].call(this, data);
    }

    function addEventListener(eventName, fn) {
        _events[eventName] = fn;
    }

    function removeEventListener(eventName) {
        delete _events[eventName];
    }

    return {
        ready: ready,
        pay: pay,
        share: share,
        addEventListener: addEventListener,
        removeEventListener: removeEventListener,
        getWXData: function () { return _wxData; },
    }
}