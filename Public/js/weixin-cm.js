 	

 	function redirectBase() {
        location.href = "https://open.weixin.qq.com/connect/oauth2/authorize?appid="+_options.APPID+"&redirect_uri="+encodeURI(_options.CODE_PATH)+"&response_type=code&scope=snsapi_base&state="+_options.CODE_STATE+"#wechat_redirect";
    }

    function checkCodeBase() {
        if (_options.CODE == "") {
            redirectBase();
        }
        else {
            login(_options.CODE);
        }
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