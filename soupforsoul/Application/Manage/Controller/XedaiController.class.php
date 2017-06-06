<?php
namespace Manage\Controller;
use Think\Controller;
use Think\Model;

class XedaiController extends Controller {

    public function index() {

        $count = M('Xedai')->count();

        $page = new \Common\Lib\Page($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow. ',' .$page->listRows;
        $list = M('Xedai')->limit($limit)->order('status desc,xcredit desc')->select();

        $this->assign('page', $page->show());
        $this->assign('vlist', $list);
        $this->display();

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
            array('xname','姓名', 16),
            array('xphone','手机号', 16),
            array('xnumber','工号', 12),
            // array('xincome','年收入基数', 12),
            // array('maxincome','借款最大值', 14),
            array('hiretime','在职时间', 20),
            array('xcredit','授信额度', 20),
            array('status','借款状态', 20)
        );

        $Model = new Model();
        $bid = session('yang_adm_param');
        $sql = "select * from zh_xedai";
        $xlsData = $Model->query($sql);

     
        $this->exportExcel($xlsName,$xlsCell,$xlsData);
    }

    //上传方法
    public function upload() {
        header("Content-Type:text/html;charset=utf-8");
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728 ;// 设置附件上传大小
        $upload->exts = array('xls', 'xlsx');// 设置附件上传类
        $upload->savePath = '/'; // 设置附件上传目录
        // 上传文件
        $info = $upload->uploadOne($_FILES['excelData']);
        $filename = './Uploads'.$info['savepath'].$info['savename'];
        //print_r($info);exit;
        if (!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        } else {// 上传成功
            $this->white_import($filename);
        }
    }

    //导入数据方法
    protected function white_import($filename) {
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.IOFactory.php");
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $sheet = $objPHPExcel->getSheet(0);
        //获取总列数
        $col = $sheet->getHighestColumn();
        //获取总行数
        $row = $sheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        $data = array();
        for ($currow = 1; $currow <= $row; $currow++) {
            //从哪列开始，A表示第一列
            for ($curcol='A'; $curcol<=$col; $curcol++) {
                //读取到的数据，保存到数组$arr中
                $address = $curcol.$currow;
                $data[$currow][] = $sheet->getCell($address)->getValue();
            }
        }
        $this->save_import($data);
    }

    //保存导入数据
    public function save_import($data) {
        $add_time = time();
        $count = 0;
        foreach ($data as $k => $v) {
            $newData = array();
            $newData['Xname'] = $data[$k][4];
            $newData['Xnumber'] = $data[$k][1];
            $newData['Xincome'] = $data[$k][2];
            $newData['maxincome'] = $data[$k][3];
            $newData['hiretime'] = $data[$k][5];
            if(M("Xedai") -> where('Xnumber = '.$data[$k][1]) -> select()){

            }else{
                if (M("Xedai")->add($newData)) {
                    $count++;
                }
            }
            
        }
        if ($count > 0) {
            $this->success('成功导入'.$count.'条用户数据', U('Xedai/index'));
        } else {
            $this->error('导入失败');
        }
    }


        //审核
    public function edit() {

        
        $id = I('id', 0, 'intval');
        $status = I('status', 0, 'intval');
        $data = M('xedai')->find($id);
        $data2[status] = $status;
        if ($data) {
            M("Xedai")->where('id = '.$id)->save($data2);
            $this->success('审核成功', U('Xedai/index'));
        } else {
            $this->error('无此用户'.$id);
        }
        
    }

    public function test(){

        $data = M('xedai')->select();

        if($data){

            for($i= 1;$i<count($data);$i++){

               $data2[hiretime] = str_replace(" ","-",$data[$i][hiretime]);
               if($data2[hiretime] != $data[$i][hiretime]){
                    M('xedai')->where('xnumber = "'.$data[$i][xnumber].'"')->save($data2);
               }
               

            }

        }
        



    }
    
}
?>