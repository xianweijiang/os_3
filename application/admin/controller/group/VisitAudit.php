<?php

namespace app\admin\controller\group;

use app\admin\controller\AuthController;
use app\admin\model\com\ForumPower;
use app\admin\model\group\Group;
use app\admin\model\group\Power;
use service\FormBuilder as Form;
use service\JsonService;
use app\admin\model\com\VisitAudit as ForumAudit;
use service\UtilService as Util;
use service\JsonService as Json;
use app\admin\model\com\ComForum as ForumModel;
use think\Db;
use think\Url;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class VisitAudit extends AuthController
{

    /**
     * @return mixed
     */
    public function index($status = '', $type = 1, $is_weibo = 0, $oid = '',$id='')
    {
        $this->assign([
            'year' => getMonth('y'),
            'real_name' => $this->request->get('real_name', ''),
            'orderCount' =>1,
            'status' => $status,
            'type' => $type,
            'id' => $id,
            'is_weibo' => $is_weibo,
            'oid' => $oid,
            'forum_list' => ForumModel::getSelectList(),
        ]);
        $this->assign('cate', ForumModel::getCatTierList());
        return $this->fetch();
    }

    /**
     * 获取帖子主题列表
     * return json
     */
    public function thread_list()
    {
        $where = Util::getMore([
            ['status', -1],
            ['tid', 0],
            ['fid', ''],
            ['title', ''],
            ['page', 1],
            ['limit', 20],
            ['real_name', ''],
            ['data',''],
            ['order','create_time desc']
        ]);
        trace($where);
        $map['status']=['egt',0];
        if($where['status']!=-1){
            $map['status']=$where['status'];
        }
        if($where['real_name']){
            $mav['uid|nickname']=['LIKE','%'.$where['real_name'].'%'];
            $uid=db('user')->where($mav)->field('uid')->select();
            $uids=array_column($uid,'uid');
            $map['uid']=['in',$uids];
        }
        if($where['fid']){
            $map['fid']=$where['fid'];
        }
        if($where['data']){
            $map['create_time']=ForumAudit::timeRange($where['data']);
        }
        return Json::successlayui(ForumAudit::get_audit_list($map,$where['page'],$where['limit'],$where['order']));
    }

    /**
     * 驳回理由填写
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.8
     */
    public function set_reason(){
        $where = Util::getMore([
            ['id', ''],
            ['is_move',0]
        ]);
        $field = [
            Form::textarea('reason','原因'),
            Form::hidden('id', $where['id']),
            Form::hidden('status', 0),
            Form::hidden('is_move', $where['is_move']),
        ];
        $form = Form::make_post_form('驳回理由',$field, Url::build('set_audit'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 审核通过
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.8
     */
    public function set_audit(){
        $where = Util::getMore([
            ['field', 'status'],
            ['id', ''],
            ['reason',''],
            ['is_move',0],
            ['value',0],
        ]);
        if(!$where['field']){
            $where = Util::postMore([
                ['id', ''],
                ['reason',''],
                ['status',0],
                ['is_move',0],
            ]);
        }else{
            $where['status']=$where['value'];
        }
        $map['id']=['in',explode(',',$where['id'])];
        $res=ForumAudit::setStatus($map,$where['status']);
        if($res){
            //关注版块

            //清除版块权限
            ForumPower::clear_cache();
            //发送消息
            if($where['is_move']){
                $temp=51;
            }elseif($where['status']==1){
                $temp=49;
            }else{
                $temp=50;
            }
            ForumAudit::where($map)->update(['reject_reason'=>$where['reason'],'audit_uid'=>$this->adminId,'audit_time'=>time(),'is_admin'=>1]);
            ForumAudit::send_message($map,$temp,$where['reason']);
            return Json::successful('修改成功!');
        }else{
            return Json::fail('修改失败!');
        }
    }

}