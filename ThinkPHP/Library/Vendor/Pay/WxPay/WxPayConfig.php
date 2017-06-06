<?php
/**
* 	配置账号信息
*/

class WxPayConfig
{
	//保存类实例的静态成员变量
	private static $_instance;

	//private标记的构造方法
	private function __construct(){
	}

	//创建__clone方法防止对象被复制克隆
	public function __clone(){
		trigger_error('Clone is not allow!',E_USER_ERROR);
	}

	//单例方法,用于访问实例的公共的静态方法
	public static function getInstance(){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	//=======【基本信息设置】=====================================
	//
	/**
	 * TODO: 修改这里配置为您自己申请的商户信息
	 * 微信公众号信息配置
	 * 
	 * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
	 * 
	 * MCHID：商户号（必须配置，开户邮件中可查看）
	 * 
	 * KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
	 * 设置地址：https://pay.weixin.qq.com/index.php/account/api_cert
	 * 
	 * APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置），
	 * 获取地址：https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN
	 * @var string
	 */

	private $APPID = '';
	public function getAPPID() { return $this->APPID; }
	public function setAPPID($v) { $this->APPID = $v; }

	private $MCHID = '';
	public function getMCHID() { return $this->MCHID; }
	public function setMCHID($v) { $this->MCHID = $v; }

	private $KEY = '';
	public function getKEY() { return $this->KEY; }
	public function setKEY($v) { $this->KEY = $v; }

	private $APPSECRET = '';
	public function getAPPSECRET() { return $this->APPSECRET; }
	public function setAPPSECRET($v) { $this->APPSECRET = $v; }

	private $NOTIFY_URL = '';
	public function getNOTIFY_URL() { return $this->NOTIFY_URL; }
	public function setNOTIFY_URL($v) { $this->NOTIFY_URL = $v; }
	
	//=======【证书路径设置】=====================================
	/**
	 * TODO：设置商户证书路径
	 * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
	 * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
	 * @var path
	 */
	private $SSLCERT_PATH = '';
	public function getSSLCERT_PATH() { return $this->SSLCERT_PATH; }
	public function setSSLCERT_PATH($v) { $this->SSLCERT_PATH = $v; }

	private $SSLKEY_PATH = '';
	public function getSSLKEY_PATH() { return $this->SSLKEY_PATH; }
	public function setSSLKEY_PATH($v) { $this->SSLKEY_PATH = $v; }

	
	//=======【curl代理设置】===================================
	/**
	 * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
	 * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
	 * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
	 * @var unknown_type
	 */
	private $CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
	public function getCURL_PROXY_HOST() { return $this->CURL_PROXY_HOST; }
	public function setCURL_PROXY_HOST($v) { $this->CURL_PROXY_HOST = $v; }

	private $CURL_PROXY_PORT = 0;//8080;
	public function getCURL_PROXY_PORT() { return $this->CURL_PROXY_PORT; }
	public function setCURL_PROXY_PORT($v) { $this->CURL_PROXY_PORT = $v; }
	
	//=======【上报信息配置】===================================
	/**
	 * TODO：接口调用上报等级，默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，
	 * 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少
	 * 开启错误上报。
	 * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
	 * @var int
	 */
	private $REPORT_LEVENL = 1;
	public function getREPORT_LEVENL() { return $this->REPORT_LEVENL; }
	public function setREPORT_LEVENL($v) { $this->REPORT_LEVENL = $v; }
}
