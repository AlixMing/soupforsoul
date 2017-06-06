<?php

namespace Api\Controller;
use Think\Controller;
use Think\Model;

class XedaiController extends Controller {

	public function index(){
		 if (isset($_GET["code"])) {
            $this->assign('code', $_GET["code"]);
        }
        else {
            $this->assign('code', "");
        }
		$this->assign('work',C(WORK));
		$this->assign('income',C(INCOME));
		$this->assign('position',C(POSITION));
		$this->assign('type',C(TYPE));
		$this->display();

	}

	public function assessment(){


		$sql = M('xedai');
		$where[Xname] = $_POST[Xname];
		$where[Xnumber] = $_POST[Xnumber];
		$count = $sql->count();
		$data = $sql->where($where)->select();
		$incomebase = $data[0][xincome] * 10000;
		$maxincome = $data[0][maxincome] * 10000;
		$Date_2 = date("Y-m-d");
		$d2 = strtotime($Date_2);
		
		if($data){
			$Date_1 = $data[0][hiretime];
			$d1 = strtotime($Date_1);
			$Days = round(($d2-$d1)/3600/24);
			$credit = $incomebase *1.5 + $Days *50;
			if($data[0][maxincome] != -1 && $credit > $maxincome){
				$credit = $maxincome;
			}
			$_POST[Xcredit] = $credit;
			$return = $sql->where($where)->save($_POST);
			if($return == 0 || $return){
				$str = array(
					'status' => 0, 
					'msg' => '', 
					'id' => $data[0][id],
				);
			}else{
				$str = array(
					'status' => 9998, 
					'msg' => '评估失败，请重新评估', 
				);
			}
		}else{
			$str = array(
				'status' => 9998, 
				'msg' => '评估失败，工号姓名不匹配', 
			);
		}
		
		$this->ajaxreturn($str);

	}

	public function assessresult($id){

		$sql = M('xedai');
		 if (isset($_GET["code"])) {
            $this->assign('code', $_GET["code"]);
        }
        else {
            $this->assign('code', "");
        }
		$data = $sql->where('id = '.$id)->find();
		$this->assign('credit',number_format($data[xcredit]));
		$this->assign('phone',$data[xphone]);
		$this->assign('status',$data[status]);
		$this->assign('id',$id);
		$this->display();

	}

	public function borrowajax(){

		$sql = M('xedai');
		
		$xedaidata = $sql->where('id = '.$_POST[id])->select();
		
		$type = $xedaidata[0][status];
		$data[status] = 1;
		if($xedaidata){
			$return = $sql->where('id = '.$_POST[id])->save($data);
			if($return == 0 || $return){
				
				if($type == 0){
					$msg = sendMsg($_POST[phone]);
				}
				
				$str = array(
					'status' => 0, 
					'msg' => '', 
				);
				
			}else{
				$str = array(
					'status' => 9998, 
					'msg' => '申请失败', 
				);
			}
		}else{
			$str = array(
					'status' => 9998, 
					'msg' => '申请失败', 
				);
		}
		$this->ajaxreturn($str);

	}


	public function userajax(){

		$sql = M('xedai');
		$data = $sql->where('openid = "'.$_POST[openid].'"')->select();
		
		if($data){
			$data[0][xcredit] = number_format($data[0][xcredit]);
			$str = array(
				'status' => 0, 
				'msg' => $data[0], 
			);
		}else{
			$str = array(
				'status' => 9998, 
				'msg' => '无此用户', 
			);
		}
		$this->ajaxreturn($str);
	}
	
	//合同流程完成
	//参数id表示申请id
	public function contract(){
		\Think\Log::write('XedaiController:contract','ALERT');
		//获取参数
		$type = I("type");//类型（1会签合同结束，根据flowid返回contractid；2放款合同结束，根据contractid进行操作）
		$id = I("flowid");//flowid(type1时表示会签流程请求id，type2时表示放款流程请求id)
		$contractid = I("contractid");//contractid	
		$status = I("status", 1, 'intval');//status 0拒绝、1通过
		$msg = I("msg");//msg 审批意见
		if ($type == 1) {//会签合同
			$xedai = M("xedai_apply")->where("flowid = ".$id)->select();
			\Think\Log::write('flowid:'.$id,'ALERT');
			if (count($xedai) > 0 && $xedai[0][status] == 6) {
				$xedaiDetail = $xedai[0];
				if ($status == 0) {//拒绝
					$data[status] = 0;
					$data[contractid] = $contractid;
					
					$data3[status] = 0;
					M("xedai_apply")->where('id = '.$xedaiDetail[id])->save($data);
					
					$record = "会签合同流程返回：拒绝，理由：".$msg;
					
					$message = "亲，您的薪e贷审核未通过，拒绝原因：" . $msg . "，如有问题请致电海控小贷0756-8336111。";
				} else {
					$data[status] = 3;
					$data[contractid] = $contractid;
					M("xedai_apply")->where('id = '.$xedaiDetail[id])->save($data);
					
					$data3[status] = 3;
					
					$record = "会签合同流程返回：批准";
					
					$message = "亲，您的薪e贷已通过审核，请耐心等待财务放款。如有疑问请致电海控小贷0756-8336111。";
				}
				
				$result = sendMsg1($xedaiDetail[phone], $message);
				\Think\Log::write($xedaiDetail[phone] . '会签审核结果短信发送结果：' . $result, 'ALERT');
				
				//添加日志
		        $data3[apply_id] = $xedaiDetail[id];
		    	$data3[user_id] = 0;
		    	$data3[create_time] = date('Y-m-d H:i:s',time());
		    	$data3[record] = $record;
		        M("xedai_record")->add($data3);
				
				$str = array(
					'status' => 0, 
					'msg' => '修改成功'
				);
			} else {//不存在
				//返回错误
				$str = array(
					'status' => 9998, 
					'msg' => '合同不存在或非会签审核中状态' 
				);
			}
		} else if ($type == 2) {
			$xedai = M("xedai_apply")->where("contractid = ".$contractid)->select();
			
			\Think\Log::write('contractid:'.$contractid.'  status:'.$status."  msg:".$msg,'ALERT');
			if (count($xedai) > 0 && $xedai[0][status] == 3) {
				$xedaiDetail = $xedai[0];
				if ($status == 0) {//拒绝
					$data[status] = 0;
					$data[flowendtime] = date('Y-m-d H:i:s',time());
					$data[flow2id] = $id;
					M("xedai_apply")->where('id = '.$xedaiDetail[id])->save($data);
					
					$record = "放款流程返回：拒绝，理由：".$msg;
					
					$data3[status] = 0;
					
//					$message = "亲，您的薪e贷审核未通过，拒绝原因：" . $msg . "，如有问题请致电海控小贷0756-8336111。";
//					$result = sendMsg1($xedaiDetail[phone], $message);
//					\Think\Log::write($xedaiDetail[phone] . '会签审核结果短信发送结果：' . $result, 'ALERT');
				} else {
					//申请处理  var_dump($xedai);
					$data[status] = 4;
					$data[flowendtime] = date('Y-m-d H:i:s',time());
					$data[flow2id] = $id;
					M("xedai_apply")->where('id = '.$xedaiDetail[id])->save($data);
					//生成还款计划
					if ($xedaiDetail[paytype] == 1) {//先息后本
						$this->xxhb($xedaiDetail);
					} else {//等额本息
						$this->debx($xedaiDetail);
					}
					$record = "放款流程返回：批准";
					
					$data3[status] = 4;
	
					//发送短信
					$message = "亲，您的薪e贷已完成放款，请注意账户资金变动。如有问题请致电海控小贷0756-8336111。";
					$result = sendMsg1($xedaiDetail[phone], $message);
					\Think\Log::write($xedaiDetail[phone] . '放款完成短信发送结果：' . $result, 'ALERT');
				}
				
				//添加日志
		        $data3[apply_id] = $xedaiDetail[id];
		    	$data3[user_id] = 0;
		    	$data3[create_time] = date('Y-m-d H:i:s',time());
		    	$data3[record] = $record;
		        M("xedai_record")->add($data3);
				
				$str = array(
					'status' => 0, 
					'msg' => '修改成功'
				);
			} else {//不存在
				//返回错误
				$str = array(
					'status' => 9998, 
					'msg' => '合同不存在或非放款中状态' 
				);
			}
		} else {
			//返回错误
			$str = array(
				'status' => 9998, 
				'msg' => 'type类型不存在' 
			);
		}
		$this->ajaxreturn($str);
	}
	
	//先息后本
	private function xxhb($xedai){
		$month = $xedai[month];//期限
		$rate = $xedai[rate];//月利率
		$num = $xedai[num];//总贷款金额
		$pernum = round(($rate*$num)/100, 2);//每个月应还利息，两位小数
		//每月还款信息
		for ($x=1; $x <= $month; $x++) {
			$data2[applyId] = $xedai[id];
			$data2[month] = $x;
			$data2[topaytime] = date('Y-m-d',strtotime("+$x month"));
			
			if ($x == $month) {
			 	$data2[amount] = round($pernum + $num, 2);
			 	$data2[unpaid] = 0;
			} else {
			 	$data2[amount] = $pernum;
			 	$data2[unpaid] = $num;	
			}
			 	
			M("xedai_pay")->add($data2);//保存
		}
	}
	
// 	//等额本息(传统银行算法)
//     private function debx($xedai){ 
// 	    $dkm   = $xedai[month]; //贷款月数，20年就是240个月 
// 	    $dkTotal = $xedai[num]; //贷款总额 
// 	    $dknl  = $xedai[rate]/100; //贷款月利率 
// //	    $emTotal = $dkTotal * $dknl / 12 * pow(1 + $dknl / 12, $dkm) / (pow(1 + $dknl / 12, $dkm) - 1); //每月还款金额
// 	    $emTotal = $dkTotal * $dknl * pow(1 + $dknl , $dkm) / (pow(1 + $dknl , $dkm) - 1); //每月还款金额
// 	    $emTotal = round($emTotal, 2);
// 	    $lxTotal = 0; //总利息 
// 	    //每月还款信息
// 	    for ($i = 1; $i <= $dkm; $i++) {
// //	    	$lx = round($dkTotal * $dknl / 12, 2);  //每月还款利息  
// 		    $lx = round($dkTotal * $dknl , 2);  //每月还款利息 
// 		    $em = round($emTotal - $lx, 2 ); //每月还款本金 
// 		    $dkTotal = round($dkTotal - $em, 2); 
// 		    $lxTotal = round($lxTotal + $lx, 2);
// 		    \Think\Log::write('第'.$i.'期,本金:'.$em.',利息:'.$lx.',总额:'.$emTotal,'ALERT');  
		    
// 		    $data2[applyId] = $xedai[id];
// 			$data2[month] = $i;
// 			$data2[topaytime] = date('Y-m-d',strtotime("+$i month"));
			
// 			if ($i == $dkm) {
// 			 	$data2[amount] = round($emTotal + $dkTotal, 2);
// 			 	$data2[unpaid] = 0;
// 			 	\Think\Log::write('第'.$i.'期,剩余总额:'.$dkTotal,'ALERT');
// 			} else {
// 			 	$data2[amount] = $emTotal;
// 			 	$data2[unpaid] = $dkTotal;
// 			 	\Think\Log::write('第'.$i.'期,剩余总额:'.$dkTotal,'ALERT');	
// 			}
// 			M("xedai_pay")->add($data2);//保存
// 	    }
//   	} 

	//等额本息
    private function debx($xedai){ 
    	$month = $xedai[month];//期限
		$rate = $xedai[rate];//月利率
		$num = $xedai[num];//总贷款金额
		$pernum = round(($rate*$num)/100 + ($num/$month), 2);//每个月应还利息，两位小数
		//每月还款信息
		for ($x=1; $x <= $month; $x++) {
			$data2[applyId] = $xedai[id];
			$data2[month] = $x;
			$data2[topaytime] = date('Y-m-d',strtotime("+$x month"));
			
			
			$data2[amount] = $pernum;
			$data2[unpaid] = round($num - ($num/$month) * $x, 2);	
	
			M("xedai_pay")->add($data2);//保存
		}
  	} 
}