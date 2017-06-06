<?php
namespace Manage\Controller;
use Think\Controller;
use Think\Model;

class ZcjsbusinessController extends Controller {

    public function index() {
        $count = M('zcjs_business')->count();

        $page = new \Common\Lib\Page($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow. ',' .$page->listRows;
        $list = M('zcjs_business')->limit($limit)->select();

        $this->assign('page', $page->show());
        $this->assign('vlist', $list);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $this->add_post();
            exit;
        }
        $this->display();
    }

    private function add_post() {

        //M验证
        $validate = array(
            array('name','require','商家名称不能为空！'), //默认情况下用正则进行验证
            array('goods','require','奖品名称不能为空！'), //默认情况下用正则进行验证
        );
        $db = M('zcjs_business');
        if (!$db->validate($validate)->create()) {
            $this->error($db->getError());
        }

        $data = I('post.');
        if ($id = $db->add($data)) {
            $this->success('添加成功', U('Zcjsbusiness/index'));
        } else {
            $this->error('添加失败');
        }
    }

    public function del() {

        $id = I('id', 0, 'intval');
        $business =  M('zcjs_business');
        if (false !== $business->delete($id)) {
            $this->success('删除成功', U('Zcjsbusiness/index'));
        } else {
            $this->error('删除失败');
        }
    }

    public function edit($id) {

        if (IS_POST) {
            $this->edit_post($id);
            exit;
        }

        $list = M('zcjs_business')->where('id='.$id)->select();
        $this->assign('vlist', $list);
        $this->assign('bid', $id);
        $this->display();
    }

    private function edit_post($id) {

        $validate = array(
            array('name','require','商家名称不能为空！'), //默认情况下用正则进行验证
            array('goods','require','奖品名称不能为空！'), //默认情况下用正则进行验证
        );

        $db = M('zcjs_business');
        if (!$db->validate($validate)->create()) {
            $this->error($db->getError());
        }

        $data = I('post.');
        if ($db->where('id='.$id)->save($data)) {
            $this->success('修改成功', U('Zcjsbusiness/index'));
        } else {
            $this->error('修改失败');
        }
    }

    public function account($id) {
        if (IS_POST) {
            $this->account_post($id);
            exit;
        }
        $business = M('zcjs_business')->where('id='.$id)->find();
        $this->assign('business', $business);
        $this->display();
    }

    private function account_post($id) {

        $validate = array(
            array('username','require','账号不能为空！'), //默认情况下用正则进行验证
            array('passowrd','require','密码不能为空！'), //默认情况下用正则进行验证
        );

        $model = M('zcjs_business');
        if (!$model->validate($validate)->create()) {
            $this->error($model->getError());
        }

        $data = I('post.');
        $data['password'] = md5($data['password']);

        $business = $model->where("username='".$data['username']."'")->find();
        if ($business) {

            if ($business["id"] != $id) {
                $this->error('账号已存在');
            }
            else {
                $condition['id'] = $id;
                if ($model->where($condition)->save($data)) {
                    $this->success('设置成功', U('Zcjsbusiness/index'));
                } else {
                    $this->error('设置失败');
                }
            }
        }
        else {
            $condition['id'] = $id;
            if ($model->where($condition)->save($data)) {
                $this->success('设置成功', U('Zcjsbusiness/index'));
            } else {
                $this->error('设置失败');
            }
        }
    }
}