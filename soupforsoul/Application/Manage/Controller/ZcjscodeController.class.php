<?php
namespace Manage\Controller;
use Think\Controller;
use Think\Model;

class ZcjscodeController extends Controller {

    public function index() {
        $bid = session('yang_adm_param');
        if (session(C('ADMIN_AUTH_KEY')) === true || intval($bid) === 0) {
            $count = M('zcjs_code')->count();
        } else {
            $count = M('zcjs_code')->where("bid=".$bid)->count();
        }

        $page = new \Common\Lib\Page($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow. ',' .$page->listRows;

        $Model = new Model();
        if (session(C('ADMIN_AUTH_KEY')) === true || intval($bid) === 0) {
            $sql = 'select a.name,a.goods,b.id,b.code,b.state,b.phone,b.create_time from zh_zcjs_business as a, zh_zcjs_code as b where a.id=b.bid order by b.id asc limit ' . $limit;
        }
        else {
            $sql = 'select a.name,a.goods,b.id,b.code,b.state,b.phone,b.create_time from zh_zcjs_business as a, zh_zcjs_code as b where a.id=b.bid and a.id='.$bid.' order by b.id asc limit ' . $limit;
        }
        $list = $Model->query($sql);

        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }

    public function detail($code) {
        //当前控制器名称
        $code = strtoupper($code);
        if (IS_POST) {
            $this->detail_post($code);
            exit;
        }

        $Model = new Model();
        $sql = "select a.name,a.goods,b.id,b.code,b.state,b.phone,b.create_time,b.exchange_time from zh_zcjs_business as a, zh_zcjs_code as b where a.id=b.bid  and b.code='".$code."'";
        $list = $Model->query($sql);
        if ($list) {
            $this->assign('vlist', $list);
            $this->assign('code', $code);
            $this->display();
        }
        else {
            $this->error('没有找到对应兑换码');
        }
    }

    private function detail_post($code) {

        $validate = array(
            array('code','require','兑换码不能为空！'),
            array('state','0','兑换码已兑换！',1,'equal'),
        );
        $db = M('zcjs_code');
        if (!$db->validate($validate)->create()) {
            $this->error($db->getError());
        }
        $newdata['state'] = 1;
        $newdata['exchange_time'] = time();
        $condition["code"] = $code;
        $bid = session('yang_adm_param');
        if (session(C('ADMIN_AUTH_KEY')) !== true && intval($bid) !== 0) {
            $condition["bid"] = intval(session('yang_adm_param'));
        }
        if ($db->where($condition)->save($newdata)) {
            $this->success('兑换成功', U('Zcjscode/index'));
        } else {
            $this->error('兑换失败,bid='.$bid);
        }
    }

    private function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $expTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->setActiveSheetIndex(0);
        $styleArray1 = array(
            'font' => array(
                'bold' => true,
                'color'=>array(
                    'argb' => '00000000',
                ),
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($cellName[$i])->setWidth($expCellName[$i][2]);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->applyFromArray($styleArray1);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        ob_end_clean();//清除缓冲区,避免乱码
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    /**
     * 导出Excel
     */
    public function expCode(){//导出Excel

        $xlsName  = "code";
        $xlsCell  = array(
            array('id','编号', 6),
            array('name','商家', 16),
            array('goods','奖品', 16),
            array('code','兑换码', 12),
            array('state','状态', 12),
            array('phone','手机号', 14),
            array('create_time','获取时间', 20),
            array('exchange_time','兑换时间', 20)
        );

        $Model = new Model();
        $bid = session('yang_adm_param');
        if (session(C('ADMIN_AUTH_KEY')) === true || intval($bid) === 0) {
            $sql = "select b.id,a.name,a.goods,b.code,b.state,b.phone,b.create_time,b.exchange_time from zh_zcjs_business as a, zh_zcjs_code as b where a.id=b.bid order by b.id asc";
        } else {
            $sql = "select b.id,a.name,a.goods,b.code,b.state,b.phone,b.create_time,b.exchange_time from zh_zcjs_business as a, zh_zcjs_code as b where a.id=b.bid and b.bid=".$bid." order by b.id asc";
        }
        $xlsData = $Model->query($sql);

        foreach ($xlsData as $k => $v) {
            $xlsData[$k]['state'] = $v['state']==0?'未兑换':'已兑换';
            $xlsData[$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
            $xlsData[$k]['exchange_time'] = date("Y-m-d H:i:s",$v['create_time']);
        }
        $this->exportExcel($xlsName,$xlsCell,$xlsData);
    }

    public function exchange($code) {

        $db = M('zcjs_code');

        $newdata['state'] = 1;
        $newdata['exchange_time'] = time();
        $condition["code"] = $code;

        if ($db->where($condition)->save($newdata)) {
            $this->success('兑换成功', U('Zcjscode/index'));
        } else {
            $this->error('兑换失败');
        }
    }
}