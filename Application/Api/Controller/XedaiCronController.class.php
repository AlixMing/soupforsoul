<?php
namespace Api\Controller;
use Think\Controller;
use Think\Model;

//定时任务
class XedaiCronController extends Controller {

//	public function index(){
//		$this -> payAlert();//还款
//		$this -> latePay();//逾期计算利息
//	}

	//还款提醒-提前三天
	public function payAlert(){
		\Think\Log::write('XedaiCronController:payAlert','ALERT');
		//1、查找所有需要提前提醒的
		$topayAlerts = M('xedai_pay a')->join(' zh_xedai_apply b ON a.applyId = b.id','LEFT')
			->where(' a.status = 0 and to_days(a.topaytime) - to_days(now()) = 3')
        	->field(' b.phone, a.amount')->select();
		//2、发送短信
		if (count($topayAlerts) > 0) {
			foreach ($topayAlerts as $k) {
				$msg = "温馨提醒，您的薪e贷将于3天后还款到期，还款金额".$k[amount]."元，请您提前准备按时还款。如已还款请忽略该条信息。";
				$result = sendMsg1($k[phone], $msg);
				\Think\Log::write('sendMsg1-phone:'.$k[phone].',msg:'.$msg.',result:'.$result,'ALERT');
			}
		}
		
		//1、查找所有当天需要提醒的
		$payAlerts = M('xedai_pay a')->join(' zh_xedai_apply b ON a.applyId = b.id','LEFT')
			->where(' a.status = 0 and to_days(a.topaytime) - to_days(now()) = 0')
        	->field(' b.phone, a.amount')->select();
		//2、发送短信
		if (count($payAlerts) > 0) {
			foreach ($payAlerts as $k) {
				$msg = "温馨提醒，您的薪e贷今天到期还款，还款金额".$k[amount]."元，请您及时还款以免产生滞纳金。如已还款请忽略该条信息。";
				$result = sendMsg1($k[phone], $msg);
				\Think\Log::write('sendMsg1-phone:'.$k[phone].',msg:'.$msg.',result:'.$result,'ALERT');
			}
		}
	}
	
	//逾期计算违约金（逾期还款金额按每日千分之二计算）
	public function latePay(){
		\Think\Log::write('XedaiCronController:latePay','ALERT');
		//1、查找所有逾期的
		$payLates = M('xedai_pay')
			->where(' status = 0 and to_days(now()) - to_days(topaytime) > 0')
			->field(' id, applyid, amount, to_days(now()) - to_days(topaytime) as days')
        	->select();
			
        //2、计算违约金以及修改逾期状态
		if (count($payLates) > 0) {
			foreach ($payLates as $k) {
				\Think\Log::write('applyid:'.$k[applyid],'ALERT');
				$penalty = round ($k[amount] * 0.002 * $k[days] , 2);
				//\Think\Log::write('违约金:'.$penalty.$k[days],'ALERT');
				
				$data[islate] = 1;
				$data[lateamount] = $penalty;
				M('xedai_pay') -> where(' id = '.$k[id]) -> save($data);
				
				$data2[paystatus] = 1;
				M('xedai_apply') -> where(' id = '.$k[applyid]) -> save($data2);
			}
		}
	}
}