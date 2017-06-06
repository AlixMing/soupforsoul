<?php
namespace Manage\Controller;
use Think\Controller;
use Think\Model;

//用户管理
class XedaiUserController extends CommonController {

    public function index() {
        $count = M('Xedai_user')->count();
        
		$other = I('other');//查找条件
		\Think\Log::write('other:'.$other,'DEBUG');
		
		if (!$other) {
			$count = M('Xedai_user')->count();
		} else {
			$count = M('Xedai_user')->where("Xnumber like '%{$other}%' OR Xname like '%{$other}%'")->count();	
		}
		
        $page = new \Common\Lib\Page($count, 15);
        $page->rollPage = 7;
        $page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow. ',' .$page->listRows;
        if (!$other) {
			$list = M('Xedai_user')->limit($limit)->order('create_time desc')->select();
		} else {
			$list = M('Xedai_user')->where("Xnumber like '%{$other}%' OR Xname like '%{$other}%'")->limit($limit)->order('create_time desc')->select();	
		}
		
		$this->assign('rownum', $page->firstRow);
        $this->assign('page', $page->show());
        $this->assign('vlist', $list);
        $this->display();
    }
    
    //修改用户状态
    public function edit() {
	    $id = I('id', 0, 'intval');
	    $status = I('status', 0, 'intval');
	    $data = M('Xedai_user')->find($id);
	    $data2[status] = $status;
	    if ($data) {
	        M('Xedai_user')->where('id = '.$id)->save($data2);
	        $this->success('修改成功', U('XedaiUser/index'));
	    } else {
	        $this->error('用户不存在');
	    }
    }
    
    //修改额度
    public function infoChange(){
		\Think\Log::write('XedaiUserController:infoChange','DEBUG');    
			
	    $id = I('id', 0, 'intval');
	    $xcredit = I('xcredit');
	    	
	    \Think\Log::write('id:'.$id.',xcredit:'.$xcredit,'DEBUG');
	    	
	    $user = M('Xedai_user')->find($id);
	    $data[Xcredit] = $xcredit;
	    
	    $oldXcredit = $user[xcredit];
	    $oldSurplus = $user[surplusmoney];
		if ($oldSurplus == 0 && !$oldXcredit) {
			$data[surplusmoney] = $xcredit;	
		} else {
			$data[surplusmoney] = $oldSurplus + $xcredit - $oldXcredit;
		}
	    
	    if (!$user) {
	        $this->error('用户不存在');
		} else {
		    M('Xedai_user')->where('id = '.$id)->save($data);
		    $this->success('修改成功');
		}	
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
        for ($currow = 2; $currow <= $row; $currow++) {
            //从哪列开始，A表示第一列
            for ($curcol='A'; $curcol<=$col; $curcol++) {
                //读取到的数据，保存到数组$arr中
                $address = $curcol.$currow;
                $data[$currow][] = $sheet->getCell($address)->getValue();
            }
        }
        var_dump($data);
        $this->save_import($data);
    }

    //保存导入数据
    public function save_import($data) {
        $add_time = date('Y-m-d H:i:s',time());
        $count = 0;
        foreach ($data as $k => $v) {
            $newData = array();
            $newData['Xname'] = $data[$k][0];
            $newData['Xnumber'] = $data[$k][1];
            $newData['Xincome'] = $data[$k][2];
            $newData['maxincome'] = $data[$k][3];
            $newData['hiretime'] = $data[$k][4];
            $newData['create_time'] = $add_time;
            if(M("Xedai_user") -> where('Xnumber = '.$data[$k][1]) -> select()){
				
            }else{
                if (M("Xedai_user")->add($newData)) {
                    $count++;
                }
            }
        }
        if ($count > 0) {
            $this->success('成功导入'.$count.'条用户数据', U('XedaiUser/index'));
        } else {
            $this->error('导入失败');
        }
    }
}
?>