 <?php
/***
*用户自定义函数文件，二次开发，可将函数写于此，升级不会覆盖此文件
***/

//XXXtest为测试数据
function xxxtest() {
	echo "xxxtest function";
}

 function https_request($url, $data = null)
 {
	 $curl = curl_init();
	 curl_setopt($curl, CURLOPT_URL, $url);
	 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	 if (!empty($data)) {
		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	 }
	 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	 $output = curl_exec($curl);
	 curl_close($curl);
	 return $output;
 }

 function api_request($url, $data, $method = "GET")
 {
	 $ch = curl_init();
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 curl_setopt($ch, CURLOPT_URL, $url);
	 //以下两行，忽略 https 证书
	 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	 $method = strtoupper($method);
	 if ($method == "POST") {
		 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		 curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		 curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	 }
	 $content = curl_exec($ch);
	 curl_close($ch);
	 return $content;
 }


 function isWeixin()
 {
	 $user_agent = $_SERVER['HTTP_USER_AGENT'];
	 if (strpos($user_agent, 'MicroMessenger') === false) {
		 // 非微信浏览器禁止浏览
		 //echo "HTTP/1.1 401 Unauthorized";
		 return false;
	 } else {
		 // 微信浏览器，允许访问
		 //echo "MicroMessenger";
		 // 获取版本号
		 //preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches);
		 //echo '<br>Version:'.$matches[2];
		 return true;
	 }
 }

 function isMobile()
 {
	 $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	 $is_iphone = (strpos($agent, 'iphone')) ? true : false;
	 $is_android = (strpos($agent, 'android')) ? true : false;

	 if ($is_iphone) {
		 return  true;
	 }

	 if ($is_android) {
		 return  true;
	 }

	 return false;
 }

?>