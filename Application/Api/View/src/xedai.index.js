


	function init(type){
		layer.closeAll();
		G_weixin.addEventListener(WEIXIN_CACHE_SUCCESS, function (data) {
	        $("#wxheadimg").attr("src", data.headimgurl);
	        $("#weixinid").val(data.openid);
	        // if(type==1){
	        // 	shownext(data.openid);
	        // }else{
	        // 	shownext(data.openid);
	        // }
	        shownext(data.openid,type);
	        G_weixin.bindShare();
	    });

	    G_weixin.addEventListener(WEIXIN_CACHE_FAIL, function (data) {
	        G_weixin.checkCodeBase();
	    });

	    G_weixin.addEventListener(WEIXIN_LOGIN_COMPLETE, function (data) {
	        $("#wxheadimg").attr("src", data.headimgurl);
	        $("#weixinid").val(data.openid);
	        // if(type==1){
	        // 	shownext(data.openid);
	        // }
	        shownext(data.openid,type);
	        G_weixin.bindShare();
	    });

	    G_weixin.loadCache();	

	}

	function shownext(openid,type){

		$.ajax({
            type: "POST",
            url: "?s=Activity/Xedai/userajax",
            dataType: "json",
            data: {"openid":openid},
           	beforeSend: function(){
            	layer.open({
					type:2,
				});
            },
            success: function(data) {
            	console.log(data);
            	if (data.status == 9998) {

                	layer.closeAll();
                	if(type == 2){
                    	window.location.href = '?s=Activity/Xedai/index';
                    }
                }
                else {
                	if(type == 1){

                		window.location.href = '?s=Activity/Xedai/assessresult/id/'+data.msg.id;

                	}else{
                		layer.closeAll();
                		
                		var list = '<div class="credit">￥<span>'+data.msg.xcredit+'</span></div>';
                		if(data.msg.status == 0){
                			list += '<img src="/haikongjinrong/Application/Activity/View/image/xedai/access01.png">';
                			list2 = '<div class="accessbutton"><a href="javascript:borrow('+data.msg.xphone+','+data.msg.id+')"><img src="/haikongjinrong/Application/Activity/View/image/xedai/botton1.png" ></a></div>';
                		}else if(data.msg.status == 1){
                			list += '<img src="/haikongjinrong/Application/Activity/View/image/xedai/access001.png">';
                			list2 = '<div class="accessbutton"><a href="tel:0756-8336111"><img src="/haikongjinrong/Application/Activity/View/image/xedai/access02.png"/></a></div>';
                		}else if(data.msg.status == 2){
                			list += '<img src="/haikongjinrong/Application/Activity/View/image/xedai/access002.png">';
                			list2 = '<div class="accessbutton"><a href="tel:0756-8336111"><img src="/haikongjinrong/Application/Activity/View/image/xedai/access02.png"/></a></div>';
                		}else{
                			list += '<img src="/haikongjinrong/Application/Activity/View/image/xedai/access003.png">';
                			list2 = '<div class="accessbutton"><a href="tel:0756-8336111"><img src="/haikongjinrong/Application/Activity/View/image/xedai/access02.png"/></a></div>';
                		}
                		$('.eduimg').html(list);
                		$('.button').html(list2);
                	}
					
                    
                }
            },

		});
	}
	function Xselect(seltype){


		$('.selectdiv').hide();
		$('.'+seltype).show();

	}


	function doassess(){

		var Xname = $("#Xname").val();
		var Xphone = $("#Xphone").val();
	    var Xnumber = $("#Xnumber").val();
	   
	    var openid = $("#weixinid").val();


	    if (isNull(Xnumber)) {
	        layer.open({
		        content: '请输入工号'
		        ,skin: 'msg'
		        ,time: 2 //2秒后自动关闭
		    });
	        return;
	    }

	    if (isNull(Xname)) {
	        layer.open({
		        content: '请输入姓名'
		        ,skin: 'msg'
		        ,time: 2 //2秒后自动关闭
		    });
	        return;
	    }
	    if (!checkPhone(Xphone)) {
	        layer.open({
		        content: '请输入正确的电话号码'
		        ,skin: 'msg'
		        ,time: 2 //2秒后自动关闭
		    });
	        return;
	    }
	    
		$.ajax({
            type: "POST",
            url: "?s=Activity/Xedai/assessment",
            dataType: "json",
            data: {"Xname":Xname,"Xnumber":Xnumber,"Xphone":Xphone,"openid":openid},
            beforeSend: function(){
            var div = '<p><img src="/haikongjinrong/Application/Activity/View/image/xedai/loading.png" style="width:100%;"/></p><div class="loding"><i></i><i class="load"></i><i></i></div>';
				layer.open({
					content:div,
				});
			    $('.layui-m-layerchild').css('background-color','initial');
			    $('.layui-m-layerchild').css('width','100%');
            },
            success: function(data) {
            	console.log(data);
                if (data.status == 9998) {
                	 setTimeout(function () {
	                    layer.closeAll();
		                layer.open({
					        content: data.msg
					        ,skin: 'msg'
					        ,time: 2 //2秒后自动关闭
					    });

		           	}, 3000);
                    
                }
                else {
                	var id = data.id;
                    setTimeout(function () {
	                   layer.closeAll();
		               window.location.href = '?s=activity/Xedai/assessresult/id/'+id;

		           	}, 3000);
                    
                }
            },
            complete: function(){
            	
            }
        });
	}





	function borrow(phone,id){

		$.ajax({
            type: "POST",
            url: "?s=Activity/Xedai/borrowajax",
            dataType: "json",
            data: {"phone":phone,"id":id},
           
            success: function(data) {
            	if (data.status == 9998) {
                	 setTimeout(function () {
		                layer.open({
					        content: data.msg
					        ,skin: 'msg'
					        ,time: 2 //2秒后自动关闭
					    });

		           	}, 3000);
                    
                }
                else {
					var div = '<p><img src="/haikongjinrong/Application/Activity/View/image/xedai/tijiao.png" style="width:100%;"/></p><p style="position: absolute;top: 18%;width: 10%;right: 8%;" onclick="btnclose('+id+')"><img src="/haikongjinrong/Application/Activity/View/image/xedai/cha.png" style="width:100%;"/></p><div class="cal"><a href="tel:0756-8336111"><img src="/haikongjinrong/Application/Activity/View/image/xedai/access02.png" style="width: 60%;margin: 0 auto;"/></a></div>';
					layer.open({
						content:div,
					});
					$('.layui-m-layerchild').css('background-color','initial');
					$('.layui-m-layerchild').css('width','100%');
                    
                }
            },

		});
	}

	function btnclose(id){
		window.location.href = '?s=activity/Xedai/assessresult/id/'+id;
	}