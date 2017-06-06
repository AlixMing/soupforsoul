<?php
return array(
	//'配置项'=>'配置值'
	'VAR_SESSION_ID' => 'PHPSESSID',//post方式 提交 session_id//Public uploadFile
	'TMPL_TEMPLATE_SUFFIX' => '.html',//模板后缀

	//去掉伪静态后缀
	'URL_HTML_SUFFIX' => '',

	'URL_MODEL' => 3, //URL模式
	//禁止路由
	'URL_ROUTER_ON' => false,
	//禁止静态缓存
	'HTML_CACHE_ON' => false,

	'TMPL_PARSE_STRING' => array(
		'__VIEW__' => __ROOT__. ltrim(APP_PATH,'.'). MODULE_NAME . '/View',
		'__VERSION__' => 140
	),
	// 海控金融, 金融公众号使用www.haikongjinrong.com，不同域名调用不了微信api
//	'WX_APPID' => 'wx40684511564978d1',
//	'WX_APPSECRET' => '59c22fdb164c30e5d7c559eb8397037c',

	// 海控商城
	'WX_APPID' => 'wx98ab118f6d56d442',
	'WX_APPSECRET' => '119ea4422e2db5780c1f92b9e1ce52fc',

	// Life宝
//	'WX_APPID' => 'wx9dd9407643b6abe9',
//	'WX_APPSECRET' => 'c9356cb6dca91ca800ef71a181a9760b',
	'WX_MCHID' => '1250809801',
	'WX_KEY' => 'greedcweixinzhifupassword1234567',
	'WX_SSLCERT_PATH' => '',
	'WX_SSLKEY_PATH' => '',

	
	'INCOME' => array(
		'0' => '小于10万', 
		'1' => '小于20万，大于等于10万', 
		'2' => '小于30万，大于等于20万', 
		'3' => '大于等于30万', 
	),
	//授信额度
	'CREDITTYPE' => array(
		'0' => '5', 
		'1' => '10', 
		'2' => '20', 
		'3' => '30', 
	),
);