<?php
namespace Manage\Controller;
use Think\Controller;
use Think\Model;
//审批管理
class XedaiManageController extends CommonController {

	public function index() {
		
		$status = I('status', -1, 'intval');
		$begintime = I('beginTime');
		$endtime = I('endTime');
		$other = I('other');
		
		\Think\Log::write('status:' . $status . ',beginTime:' . $begintime . ',endTime:' . $endtime, 'ALERT');
		
		$where = ' status <> -1';
		$rwhere = ' 1 = 1';
		//拒绝
		$awhere = ' 1 = 1';
		//接受
		if ($status != -1) {
			$where .= ' and status = ' . $status;
			if ($status == 0) {
				$rwhere .= ' and status = 0';
				$awhere .= ' and status < 0';
			} else if ($status == 1) {
				$rwhere .= ' and status < 0';
				$awhere .= ' and status < 0';
			} else {
				$rwhere .= ' and status < 0';
				$awhere .= ' and status = ' . $status;
			}
		} else {
			$rwhere .= ' and status = 0';
			$awhere .= ' and status > 1';
		}
		if ($begintime != '') {
			$begin = $begintime . ' 00:00:00';
			$end = $endtime . ' 23:59:59';
			$where .= " and unix_timestamp( applytime ) between unix_timestamp( '" . $begin . "' ) and unix_timestamp( '" . $end . "' ) ";
			$rwhere .= " and unix_timestamp( applytime ) between unix_timestamp( '" . $begin . "' ) and unix_timestamp( '" . $end . "' ) ";
			$awhere .= " and unix_timestamp( applytime ) between unix_timestamp( '" . $begin . "' ) and unix_timestamp( '" . $end . "' ) ";
		}
		if ($other != '') {
			$where .= " and name like '%{$other}%' OR worknum like '%{$other}%' OR phone like '%{$other}%'";
		}
		$count = M('xedai_apply') -> where($where) -> count();
		\Think\Log::write('where:' . $where . ',count:' . $count, 'ALERT');

		$page = new \Common\Lib\Page($count, 20);
		$page -> rollPage = 7;
		$page -> setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
		$limit = $page -> firstRow . ',' . $page -> listRows;
		$list = M('xedai_apply') -> where($where) -> limit($limit) -> order(' field(status,5,4,3,6,2,1,0),checktime desc,applytime desc') -> select();
		
		
		 
		$this -> assign('rownum', $page -> firstRow);
		$this -> assign('page', $page -> show());
		$this -> assign('vlist', $list);
		$this -> assign('status', $status);
		$this -> assign('beginTime', $begintime);
		$this -> assign('endTime', $endtime);
		$this -> assign('other', $other);
		$this -> assign('count', M('xedai_apply') -> where($where) -> count());
		$this -> assign('refuse', M('xedai_apply') -> where($rwhere) -> count());
		$this -> assign('accept', M('xedai_apply') -> where($awhere) -> count());
		$this -> display();
	}

	//审核或拒绝
	public function edit() {
		$userid = session(C('USER_AUTH_KEY'));
		if (!$userid) {
			$this -> error('会话无效，请重新登录！');
		} else {
			$id = I('id', 0, 'intval');
			$reason = I('reason');
			$status = I('status', 0, 'intval');
			$data = M('xedai_apply') -> find($id);
			$data2[checktime] = date('Y-m-d H:i:s', time());
			//审核时间
			$data2[checkerid] = $userid;
			if ($data) {
				$data2[status] = $status;
				$data2[reason] = $reason;
				M("xedai_apply") -> where('id = ' . $id) -> save($data2);

				//添加日志
				if ($status == 0) {
					$record = "本地审核结果：拒绝。理由：" . $reason;
				} else {
					$record = "本地审核结果：通过";
				}
				$this -> addRecord($id, $userid, $status, $record);

				//发送短信
				if ($status == 2) {
					//$msg = "亲，您的薪e贷已通过审核，请耐心等待财务放款。如有疑问请致电海控小贷0756-8336111。";
				} else {//拒绝额度返回
					$msg = "亲，您的薪e贷审核未通过，拒绝原因：" . $reason . "，如有问题请致电海控小贷0756-8336111。";
					M("xedai_user") -> where(' status = 1 and xnumber = ' . $data[worknum]) -> setInc('surplusmoney', $data[num]);
					
					$result = sendMsg1($data[phone], $msg);
					\Think\Log::write($data[phone] . '审核结果短信发送结果：' . $result, 'ALERT');
				}

				$this -> success('状态修改成功', U('XedaiManage/index'));
			} else {
				M("xedai_apply") -> where('id = ' . $id) -> save($data2);
				$this -> error('查找不到该申请记录' . $id);
			}
		}
	}

	//查看详情（如果已还款，同时查看还款情况）
	public function check() {
		\Think\Log::write('XedaiManageController:check', 'ALERT');

		$id = I('id', 0, 'intval');
		$applyDetail = M('xedai_apply a') -> where(' a.id = ' . $id) -> field('a.id,a.name,a.phone,a.worknum,INSERT(a.iden,14,4,"****") as iden,INSERT(a.banknum,14,4,"****") as banknum,format(a.limitnum,0) as limitnum,format(a.num,0) as num,
a.rate,a.month,a.paidmonth,a.status,a.paystatus,a.applytime,a.checktime,a.flowendtime, a.bankname, a.reason, a.isearlypay') -> select();
		if (!$applyDetail) {
			$this -> error('查找不到该申请记录' . $id);
		}

		$payDetail = M('xedai_pay') -> where(' applyid = ' . $id) -> order(' month') -> select();
		$recordDetail = M('xedai_record a') -> join(' zh_admin b ON a.user_id = b.id', 'LEFT') -> where(' a.apply_id = ' . $id) -> field('a.record,a.create_time,b.username as user') -> order(' a.create_time desc') -> select();

		$this -> assign('detail', $applyDetail[0]);
		$this -> assign('payDetail', $payDetail);
		$this -> assign('recordDetail', $recordDetail);
		$this -> display();
	}

	//线下还款
	public function pay() {
		\Think\Log::write('XedaiManageController:pay', 'ALERT');
		$userid = session(C('USER_AUTH_KEY'));
		if (!$userid) {
			$this -> error('会话无效，请重新登录！');
		} else {
			$id = I('id', 0, 'intval');
			//xedai_pay
			$payDetail = M('xedai_pay') -> find($id);
			\Think\Log::write('id:' . $id, 'ALERT');
			if (!$payDetail) {
				$this -> error('查找不到该还款记录' . $id);
			}
			if ($payDetail[status] == 1) {
				$this -> error('该月已还款');
			} else {
				$status = 4;
				//修改支付状态
				$data2[status] = 1;
				$data2[paid] = round($payDetail[amount] + $payDetail[lateamount], 2);
				//应还+逾期
				$data2[paytime] = date('Y-m-d H:i:s', time());
				M("xedai_pay") -> where('id = ' . $id) -> save($data2);

				$payDetailList = M('xedai_pay') -> where(' status = 0 and applyid = ' . $payDetail[applyid]) -> order(' month') -> select();

				//修改申请状态
				$apply = M('xedai_apply') -> find($payDetail[applyid]);
				$data[paidmonth] = $apply[paidmonth] + 1;
				if (count($payDetailList) == 0) {//完成
					$data[status] = 5;
					$status = 5;

					M("xedai_user") -> where(' status = 1 and xnumber = ' . $apply[worknum]) -> setInc('surplusmoney', $apply[num]);
				}
				M("xedai_apply") -> where('id = ' . $payDetail[applyid]) -> save($data);

				//添加日志
				$record = '第' . $payDetail[month] . '期还款成功，金额：' . $payDetail[amount];
				$this -> addRecord($payDetail[applyid], $userid, $status, $record);

				$this -> success('还款成功', U('XedaiManage/check/id/' . $payDetail[applyid]));
			}
		}
	}

	//提前还款
	public function payAll() {
		\Think\Log::write('XedaiManageController:payAll', 'ALERT');
		$userid = session(C('USER_AUTH_KEY'));
		if (!$userid) {
			$this -> error('会话无效，请重新登录！');
		} else {
			$applyId = I('applyId', 0, 'intval');
			//xedai_pay
			$data = M('xedai_apply') -> find($applyId);
			if ($data) {
				$amount = I('amount');

				$data3[status] = 1;
				$data2[paytime] = date('Y-m-d H:i:s', time());
				M("xedai_pay") -> where('applyid = ' . $applyId) -> save($data3);

				$data2[earlypaytime] = date('Y-m-d H:i:s', time());
				$data2[earlypayuser] = $userid;
				$data2[earlypaynum] = $amount;
				$data2[isearlypay] = 1;
				$data2[status] = 5;
				M("xedai_apply") -> where('id = ' . $applyId) -> save($data2);

				//还款完成，额度归还
				M("xedai_user") -> where(' status = 1 and xnumber = ' . $data[worknum]) -> setInc('surplusmoney', $data[num]);

				//添加日志
				$record = '提前还款成功，金额：' . $amount;
				$this -> addRecord($applyId, $userid, 5, $record);

				$this -> success('还款成功', U('XedaiManage/check/id/' . $applyId));
			} else {
				$this -> error('查找不到该贷款记录' . $applyId);
			}
		}
	}

	//发起流程
	public function flow() {
		\Think\Log::write('XedaiManageController:flow', 'ALERT');
		$userid = session(C('USER_AUTH_KEY'));
		if (!$userid) {
			$str = array('status' => 9998, 'msg' => '会话无效，请重新登录！');
		} else {
			$id = I('id', 0, 'intval');
			$worknum = I('worknum');
			$data = M('xedai_apply') -> find($id);
			$now = date('Y-m-d', time());
			if ($data) {
				//文件上传
				$filePostUrl = C(XEDAI_FILE_POST_URL);
				$file = 'Downloads/' . $this -> generateWord($id);
				$post["workflowId"] = C(FLOW_ID);
				$post["fileName"] = $data[name] . '薪e贷申请合同';
				$post["fileType"] = 'docx';
				$post["empCode"] = $worknum;
				$result = send_file($filePostUrl, $post, $file);
				$json = json_decode($result);
				\Think\Log::write('文件上传返回：'.$json, 'ALERT');
				if ($json -> status) {//上传成功
					//调用流程接口
					$client = new \SoapClient(C(XEDAI_OA_URL));

					$creatorId = $worknum;
					$createTime = $now;
					$requestName = '薪e贷合同会签付款申请';
					$jiekr = $data[worknum];
					$shenqrq = date('Y-m-d', strtotime($data[applytime]));
					$biaot = '付款申请';
					$danbfs = '无抵押';
					$daikje = $data[num];
					$lil = $data[rate] . '%';
					$jiekqx = $data[month] . '个月';
					if ($data[paytype] == 1) {
						$jixfs = '先息后本';
					} else {
						$jixfs = '等额本息';
					}
					$huankfs = '';
					$shoukzh = $data[banknum];
					$shoukfkhh = $data[bankname];
					$zhuynr = $data[name] . '-薪e贷-贷款' . $data[num] . '元';
					$fuj = $json -> obj -> docId;

					$myClass = oAPara($creatorId, $createTime, $requestName, $jiekr, $shenqrq, $biaot, $danbfs, $daikje, $lil, $jiekqx, $jixfs, $huankfs, $shoukzh, $shoukfkhh, $zhuynr, $fuj);

					$para = array("in0" => $myClass, "in1" => '0');
					try {
						$return = $client -> doCreateWorkflowRequest($para);

						//信息修改
						$data2[flowtime] = date('Y-m-d H:i:s', time());
						//审核时间
//						$data2[status] = 3;
						$data2[status] = 6;
						//状态改为放款中
						$data2[flowerid] = $userid;
						$data2[flowid] = $return -> out;
						M("xedai_apply") -> where('id = ' . $id) -> save($data2);

						//添加日志
						$record = "发起合同会签与放款流程";
						$this -> addRecord($id, $userid, 6, $record);

						$str = array('status' => 0, 'msg' => '流程发起成功');
					} catch (\Exception $e) {
						\Think\Log::write('流程发起失败。' . $e, 'ERROR');
						$str = array('status' => 9998, 'msg' => '发起失败。原因：' . $e -> getMessage());
					}
				} else {//失败
					$str = array('status' => 9998, 'msg' => '发起失败。原因：附件上传失败。');
				}

			} else {
				$str = array('status' => 9998, 'msg' => '无此申请记录' . $id);
			}
		}
		$this -> ajaxreturn($str);
	}

	//修改利率
	public function infoChange() {
		\Think\Log::write('XedaiManageController:rateChange', 'DEBUG');

		$userid = session(C('USER_AUTH_KEY'));
		if (!$userid) {
			$str = array('status' => 9998, 'msg' => '会话无效，请重新登录！');
		} else {
			$id = I('id', 0, 'intval');
			$rate = I('rate');
			$num = I('num');

			\Think\Log::write('id:' . $id . ',rate:' . $rate . ',num:' . $num, 'DEBUG');

			$apply = M('xedai_apply') -> find($id);
			$oldRate = $apply[rate];
			if (!$apply) {
				$str = array('status' => 9998, 'msg' => '无此用户' . $id);
			} else {
				$data2[rate] = $rate;
				$data2[num] = $num;
				M("xedai_apply") -> where('id = ' . $id) -> save($data2);

				//添加日志
				$record = "月利率由" . $oldRate . "修改为：" . $rate;
				\Think\Log::write($record, 'DEBUG');
				$this -> addRecord($id, $userid, $apply[status], $record);
				$str = array('status' => 0, 'msg' => '修改成功');
			}
		}
		$this -> ajaxreturn($str);
	}

	//逾期的修改状态
	private function changePayStatus() {
		$data1[paystatus] = 0;
		$data2[paystatus] = 1;
		$applyList = M('xedai_apply') -> where('status = 4') -> select();
		foreach ($applyList as $row) {
			$id = $row['id'];
			$payList = M('xedai_pay') -> where('status = 0 and (TO_DAYS(NOW()) - TO_DAYS(topaytime) > 0 ) and applyid = ' . $id) -> select();
			if (count($payList) > 0) {
				M('xedai_apply') -> where('id = ' . $id) -> save($data2);
			} else {
				M('xedai_apply') -> where('id = ' . $id) -> save($data1);
			}
		}
	}

	/**
	 * 导出Excel
	 */
	public function expCode() {//导出Excel
		$status = I("status");
		$begintime = I('beginTime');
		$endtime = I('endTime');
		$other = I('other');
		
		\Think\Log::write('XedaiManageController:expCode', 'DEBUG');
		\Think\Log::write('status:' . $status, 'DEBUG');

		$xlsName = "code";
		$xlsCell = array( array('id', '编号', 6), array('name', '申请人', 12), array('phone', '手机号', 16), array('worknum', '工号', 8), array('iden', '身份证号', 20), array('bankname', '银行', 20), array('banknum', '银行卡号', 20),
		//          array('limitnum','申请金额(元)', 15),
		array('num', '实贷金额(元)', 15), array('rate', '月利率(%)', 15), array('month', '期限', 8), array('paidmonth', '已还期数', 12), array('status', '借款状态', 12), array('paystatus', '是否逾期', 12), array('applytime', '申请时间', 20), array('checktime', '审核时间', 20), array('flowendtime', '放款时间', 20));

		$where = ' a.status <> -1';
		if ($status != -1) {
			$where .= ' and a.status = ' . $status;
		}
		if ($begintime != '') {
			$begin = $begintime . ' 00:00:00';
			$end = $endtime . ' 23:59:59';
			$where .= " and unix_timestamp( a.applytime ) between unix_timestamp( '" . $begin . "' ) and unix_timestamp( '" . $end . "' ) ";
		}
		if ($other != '') {
			$where .= " and a.name like '%{$other}%' OR a.worknum like '%{$other}%' OR a.phone like '%{$other}%'";
		}
		$xlsData = M('xedai_apply a') -> where($where) -> order('field(a.status,5,4,3,6,2,1,0),a.applytime desc') -> field('a.id,a.name,a.phone,a.worknum,INSERT(a.iden,14,4,"****") as iden,INSERT(a.banknum,14,4,"****") as banknum,format(a.num,0) as num,
a.rate,a.month,a.paidmonth,a.status,a.paystatus,a.applytime,a.checktime,a.flowendtime,a.bankname') -> select();

		$this -> exportExcel($xlsName, $xlsCell, $xlsData);
	}

	private function exportExcel($expTitle, $expCellName, $expTableData) {
		$xlsTitle = iconv('utf-8', 'gb2312', $expTitle);
		//文件名称
		$fileName = $expTitle . date('_YmdHis');
		//or $xlsTitle 文件名称可根据自己情况设定
		$cellNum = count($expCellName);
		$dataNum = count($expTableData);
		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.Writer.Excel5");
		import("Org.Util.PHPExcel.IOFactory.php");

		$objPHPExcel = new \PHPExcel();
		$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

		$objPHPExcel -> setActiveSheetIndex(0);
		//标题样式
		$styleArray1 = array('font' => array('bold' => true, 'color' => array('argb' => '00000000', ), ), 'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER, ), );
		//正文样式
		$styleArray2 = array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER, ), );
		for ($i = 0; $i < $cellNum; $i++) {
			$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue($cellName[$i] . '1', $expCellName[$i][1]);
			$objPHPExcel -> setActiveSheetIndex(0) -> getColumnDimension($cellName[$i]) -> setWidth($expCellName[$i][2]);
			$objPHPExcel -> getActiveSheet() -> getStyle($cellName[$i] . '1') -> applyFromArray($styleArray1);
		}
		// Miscellaneous glyphs, UTF-8
		for ($i = 0; $i < $dataNum; $i++) {

			for ($j = 0; $j < $cellNum; $j++) {
				if ($j == 0) {//编号列
					$objPHPExcel -> getActiveSheet() -> setCellValue($cellName[$j] . ($i + 2), $i + 1);
					//编号递增
				} else if ($j == 11) {//状态
					if ($expTableData[$i][$expCellName[$j][0]] == 0) {
						$objPHPExcel -> getActiveSheet() -> setCellValue($cellName[$j] . ($i + 2), '已拒绝');
					} else if ($expTableData[$i][$expCellName[$j][0]] == 1) {
						$objPHPExcel -> getActiveSheet() -> setCellValue($cellName[$j] . ($i + 2), '待审批');
					} else if ($expTableData[$i][$expCellName[$j][0]] == 2) {
						$objPHPExcel -> getActiveSheet() -> setCellValue($cellName[$j] . ($i + 2), '已通过');
					} else if ($expTableData[$i][$expCellName[$j][0]] == 3) {
						$objPHPExcel -> getActiveSheet() -> setCellValue($cellName[$j] . ($i + 2), '放款中');
					} else if ($expTableData[$i][$expCellName[$j][0]] == 4) {
						$objPHPExcel -> getActiveSheet() -> setCellValue($cellName[$j] . ($i + 2), '还款中');
					} else {
						$objPHPExcel -> getActiveSheet() -> setCellValue($cellName[$j] . ($i + 2), '已完成');
					}
				} else if ($j == 12) {//是否逾期
					if ($expTableData[$i][$expCellName[$j][0]] == 0) {
						$objPHPExcel -> getActiveSheet() -> setCellValue($cellName[$j] . ($i + 2), '否');
					} else {
						$objPHPExcel -> getActiveSheet() -> setCellValue($cellName[$j] . ($i + 2), '是');
					}
				} else {
					$objPHPExcel -> getActiveSheet() -> setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);
				}
				$objPHPExcel -> getActiveSheet() -> getStyle($cellName[$j] . ($i + 2)) -> applyFromArray($styleArray2);
			}
		}

		ob_end_clean();
		//清除缓冲区,避免乱码
		header('pragma:public');
		header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
		header("Content-Disposition:attachment;filename=$fileName.xls");
		//attachment新窗口打印inline本窗口打印
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter -> save('php://output');
		exit ;
	}

	//生成合同
	private function generateWord($id = 0) {
		//		$id = I('id', 0, 'intval');
		$data = M('xedai_apply') -> find($id);

		$file = $data[worknum] . $data[name] . '.docx';
		$file = iconv('UTF-8', 'GB18030', $file);
		//处理文件名中文

		import("Org.Util.PHPWord");
		// New Word Document
		$PHPWord = new \PHPWord();

		$document = $PHPWord -> loadTemplate('Template.docx');

		$document -> setValue('name', $data[name]);
		$document -> setValue('iden', $data[iden]);
		$document -> setValue('num', $data[num] . '元');
		$document -> setValue('rate', $data[rate]);
		$document -> setValue('bankname', $data[bankname]);
		$document -> setValue('banknum', $data[banknum]);
		$document -> setValue('year', date('Y'));
		//年
		$document -> setValue('month', $data[month]);
		//申请期限

		$document -> save('Downloads/' . $file);

		return $file;
	}

	//文件下载
	private function downFile($file) {
		if (file_exists($file)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
			exit ;
		}
	}

	//合同导出
	public function exportWord() {
		\Think\Log::write('XedaiManageController:exportWord', 'ALERT');

		$id = I('id', 0, 'intval');
		//		$data = M('xedai_apply')->find($id);
		//
		//		$file = $data[worknum].$data[name].'.docx';
		//		$file = iconv( 'UTF-8', 'GB18030', $file );//处理文件名中文
		//
		//		import("Org.Util.PHPWord");
		//		// New Word Document
		//		$PHPWord = new \PHPWord();
		//
		//		$document = $PHPWord->loadTemplate('Template.docx');
		//
		//		$document->setValue('name', $data[name]);
		//		$document->setValue('iden', $data[iden]);
		//		$document->setValue('num', $data[num].'元');
		//		$document->setValue('rate', $data[rate]);
		//		$document->setValue('bankname', $data[bankname]);
		//		$document->setValue('banknum', $data[banknum]);
		//		$document->setValue('year', date('Y'));//年
		//		$document->setValue('month', $data[month]);//申请期限
		//
		//		$document->save('Downloads/'.$file);

		$file = $this -> generateWord($id);

		$this -> downFile('Downloads/' . $file);
	}

	//添加日志
	private function addRecord($id, $userid, $status, $record) {
		$data[apply_id] = $id;
		$data[user_id] = $userid;
		$data[status] = $status;
		$data[create_time] = date('Y-m-d H:i:s', time());
		$data[record] = $record;
		M("xedai_record") -> add($data);
	}

}
?>