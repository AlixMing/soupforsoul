/**
 * Created by ljp on 2015/9/29.
 */

var WEIXIN_PAY_SUCCESS = "paysuccess";
var WEIXIN_PAY_CANCEL = "paycancel";
var WEIXIN_SHARE_SUCCESS = "sharesuccess";
var WEIXIN_SHARE_CANCEL = "sharecancel";
var WEIXIN_LOGIN_COMPLETE = "logincomplete";
var WEIXIN_CACHE_SUCCESS = "cachesuccess";
var WEIXIN_CACHE_FAIL = "cachefail";

function Weixin(options) {

    var _events = {};
    var _options = options || {
            APPID:"",
            APPSECRET:"",
            DEBUG:false,
            PREFIX:"",

            TOKEN_PATH:"",
            GETTICKET_PATH:"",
            USERINFO_PATH:"",
            ACCESS_TOKEN_PATH:"",

            SHARE_TITLE:"分享标题",
            SHARE_DESC:"分享描述",
            SHARE_URL:window.location.href,
            SHARE_ICON:"",

            PAY_PATH:"",

            CODE:"",
            CODE_PATH:"",
            CODE_STATE:"123"
        };

    var _nickname = "";
    var _headimgurl = "";
    var _openid = "";

    function bindShare() {
        var param = {};
        // param.appid = _options.APPID;
        // param.secret = _options.APPSECRET;
        $.ajax({
            type: "GET",
            url: _options.TOKEN_PATH,
            data: param,
            dataType: "json",
            success: function (data) {
                if (data.hasOwnProperty("errcode")) {
                    //alert(msg.errcode);
                }
                else {
                    getticket(data.access_token);
                }
            }
        });
    }

    function randomString(len) {
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

    function getticket(access_token) {

        var param = {};
        param.access_token = access_token;
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
                    var noncestr = randomString(16);
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
                        wx.onMenuShareAppMessage({
                            title: _options.SHARE_TITLE, // 分享标题
                            desc: _options.SHARE_DESC, // 分享描述
                            link: _options.SHARE_URL + "&openid=" + _openid, // 分享链接
                            imgUrl: _options.SHARE_ICON, // 分享图标
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
                            title: _options.SHARE_TITLE, // 分享标题
                            link: _options.SHARE_URL + "&openid=" + _openid, // 分享链接
                            imgUrl: _options.SHARE_ICON, // 分享图标
                            success: function () {
                                handle(WEIXIN_SHARE_SUCCESS, {});
                            },
                            cancel: function () {
                                handle(WEIXIN_SHARE_CANCEL, {});
                            }
                        });
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

    function pay(param) {

        $.ajax({
            type: "GET",
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

    function login(code) {
        var param = {};
        param.code = code;
        $.ajax({
            type: "GET",
            url: _options.ACCESS_TOKEN_PATH,
            data: param,
            dataType: "json",
            success: function (data) {
                if (data.hasOwnProperty("errcode")) {
                    //alert(msg.errcode);
                }
                else {
                    getinfo(data.access_token, data.openid);
                }
            }
        });
    }
    

    function getinfo(access_token, openid) {
        var param = {};
        param.access_token = access_token;
        param.openid = openid;
        $.ajax({
            type: "GET",
            url: _options.USERINFO_PATH,
            data: param,
            dataType: "json",
            success: function (data) {
                if (data.hasOwnProperty("errcode")) {
                    //alert(msg.errcode);
                }
                else {
                    _nickname = data.nickname;
                    _headimgurl = data.headimgurl;
                    _openid = data.openid;

                    handle(WEIXIN_LOGIN_COMPLETE, data);
                }
            }
        });
    }

    function redirect() {
        location.href = "https://open.weixin.qq.com/connect/oauth2/authorize?appid="+_options.APPID+"&redirect_uri="+encodeURI(_options.CODE_PATH)+"&response_type=code&scope=snsapi_userinfo&state="+_options.CODE_STATE+"#wechat_redirect";
    }

    function redirectBase() {
        location.href = "https://open.weixin.qq.com/connect/oauth2/authorize?appid="+_options.APPID+"&redirect_uri="+encodeURI(_options.CODE_PATH)+"&response_type=code&scope=snsapi_base&state="+_options.CODE_STATE+"#wechat_redirect";
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
    
    function checkCode() {
        if (_options.CODE == "") {
            redirect();
        }
        else {
            login(_options.CODE);
        }
    }
    function checkCodeBase() {
        if (_options.CODE == "") {
            redirectBase();
        }
        else {
            login(_options.CODE);
        }
    }

    function loadCache() {
        var wx = localStorage.getItem(_options.PREFIX + "_weixin");
        if (wx) {

            try {
                var json = JSON.parse(wx);
                _openid = json.openid;
                _headimgurl = json.headimgurl;
                _nickname = json.nickname;
                handle(WEIXIN_CACHE_SUCCESS, json);
            }
            catch (e) {
                handle(WEIXIN_CACHE_FAIL, {});
            }
        }
        else {
            handle(WEIXIN_CACHE_FAIL, {});
        }
    }

    function saveCache(openid, nickname, headimgurl) {
        var data = {};
        data.openid = openid;
        data.nickname = nickname;
        data.headimgurl = headimgurl;

        var json = JSON.stringify(data);
        localStorage.setItem(_options.PREFIX + "_weixin", json);
    }

    return {
        bindShare: bindShare,
        pay: pay,
        login: login,
        redirect: redirect,
        redirectBase: redirectBase,
        checkCode: checkCode,
        checkCodeBase: checkCodeBase,
        loadCache: loadCache,
        saveCache: saveCache,
        addEventListener: addEventListener,
        removeEventListener: removeEventListener,
        getOpenid: function () { return _openid; },
        getHeadimgurl: function () { return _headimgurl; },
        getNickname: function () { return _nickname; },
    }
}