<?php
/**
 * Created by PhpStorm.
 * User: zxh
 * Date: 2019/9/29
 * Time: 10:07
 */

namespace app\admin\controller\com;


use app\admin\controller\AuthController;
use app\osapi\model\com\Report;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Request;
use think\Url;
use app\admin\model\store\StoreVisit;
use app\admin\model\system\SystemAdmin;


class ComReport extends AuthController
{
    /**
     * 举报首页
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.29
     */
    public function index(){
        $where = Util::getMore([
            ['is_deal',''],
        ],$this->request);
        $reason=Report::getReportReason();
        $deal_type=Report::getDealType();
        $report_type=Report::getReportType();
        $this->assign([
            'reason'=>$reason,
            'is_deal'=>$where['is_deal'],
            'deal_type'=>$deal_type,
            'report_type'=>$report_type,
            'year'=>getMonth('y'),
            'real_name'=>$this->request->get('real_name',''),
        ]);
        return $this->fetch();
    }

    public function get_forum_report_list(){
        $where = Util::getMore([
            ['status',''],
            ['page',1],
            ['limit',10],
            ['deal_type',''],
            ['type',''],
            ['reason',''],
            ['real_name',''],
            ['user_type','to_uid'],
            ['data',''],
            ['is_deal',''],
            ['order','create_time desc'],
        ],$this->request);
        if($where['status']){
            $map['status']=$where['status'];
        }else{
            $map['status']=['neq',-2];
        }
        if($where['is_deal']!='')  $map['is_deal']=$where['is_deal'];
        if($where['reason'])  $map['reason']=$where['reason'];
        if($where['type'])  $map['type']=$where['type'];
        if($where['deal_type'])  $map['deal_type']=$where['deal_type'];
        if($where['data']){
            $map['create_time']=self::timeRange($where['data']);
        }
        if($where['real_name']){
            $mav['nickname|uid']=['LIKE','%'.$where['real_name'].'%'];
            $user=db('user')->where($mav)->field('uid')->select();
            $uid=array_column($user,'uid');
            $map[$where['user_type']]=['in',$uid];
        }
        return JsonService::successlayui(Report::getForumReportList($map,$where['page'],$where['limit'],$where['order']));
    }

    /**
     * 举报用户
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.29
     */
    public function user(){
        $reason=Report::getReportReason();
        $this->assign('reason',$reason);
        return $this->fetch();
    }

    public function get_user_report_list(){
        $where = Util::getMore([
            ['status',''],
            ['page',1],
            ['limit',10],
            ['reason',''],
            ['order','create_time desc'],
        ],$this->request);
        if($where['status']){
            $map['status']=$where['status'];
        }else{
            $map['status']=['neq',-2];
        }
        if($where['reason'])  $map['reason']=$where['reason'];

        return JsonService::successlayui(Report::getUserReportList($map,$where['page'],$where['limit'],$where['order']));
    }

    public function reason(){
        return $this->fetch();
    }

    public function reason_list(){
        return JsonService::successlayui(Report::getReportReasonList());
    }

    public function create()
    {
        $field = [
            Form::input('name', '举报理由'),
            Form::input('sort', '排序'),
        ];
        $form = Form::make_post_form('添加理由', $field, Url::build('save'), 2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'id',
            'name',
            'sort',
        ],$request);
        if(!$data['name']) return Json::fail('请输入举报理由');
        if($data['sort']<0) return Json::fail('请输入排序');

        if($data['id']){
            $res=db('report_reason')->where(['id'=>$data['id']])->update($data);
            $info='修改举报理由';
        }else{
            $res=db('report_reason')->insert($data);
            $info='创建举报理由';
        }
        if($res) {
            return Json::successful($info.'成功!');
        }else{
            return Json::fail($info.'失败!');
        }
    }


    /**编辑通知模板
     * @param $id
     * @return mixed|void
     */
    public function edit()
    {
        $id=osx_input('id',0,'intval');
        $data = db('report_reason')->where(['id'=>$id])->find();
        if(!$data) return JsonService::fail('数据不存在!');

        $this->assign('info',$data);
        return $this->fetch('edit');
    }

    /**
     * 删除投诉理由
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.30
     */
    public function delete(){
        $id=osx_input('id',0,'intval');
        $res = db('report_reason')->where(['id'=>$id])->update(['status'=>-1]);
        if($res) {
            return Json::successful('删除成功!');
        }else{
            return Json::fail('删除失败!');
        }
    }

    /**
     * 处理帖子
     * @author zxh  zxh@ourstu.com
     *时间：2019.10.08
     */
    public function delete_forum(){
        $ids=osx_input('ids','','text');
        $status=osx_input('status','','text');
        if(!$ids) return  Json::fail('请选择删除的帖子!');
        if($status==-1){
            $report=db('forum_report')->where(['id'=>['IN',$ids]])->select();
            $forum=[];
            foreach ($report as $vo){
                if($vo['type']==1){
                    $forum[]=$vo['content'];
                }
            }
            $result1=db('com_post')->where(['id'=>['IN',$ids]])->update(['status',-1]);
            $result2=db('com_thread')->where(['id'=>['IN',$ids]])->update(['status',-1]);
            if($result1!==false&&$result2!==false){
                $result=1;
            }else{
                $result=0;
            }
        }else{
            $result=1;
        }
        if($result==1) {
            $res=db('forum_report')->where(['status'=>['IN',$ids]])->update(['is_deal',1]);
        }else{
            $res=false;
        }
        if($res) {
            return Json::successful('删除成功!');
        }else{
            return Json::fail('删除失败!');
        }
    }

    private function timeRange($op){
        if (is_array($op)) {
            $range = $op;
        } else {
            // 使用日期表达式
            switch (strtolower($op)) {
                case 'today':
                case 'd':
                    $range = [ strtotime('today'),  strtotime('tomorrow')];
                    break;
                case 'week':
                case 'w':
                    $range = [ strtotime('this week 00:00:00'),  strtotime('next week 00:00:00')];
                    break;
                case 'month':
                case 'm':
                    $range = [strtotime('first Day of this month 00:00:00'), strtotime('first Day of next month 00:00:00')];
                    break;
                case 'year':
                case 'y':
                    $range = [ strtotime('this year 1/1'),  strtotime('next year 1/1')];
                    break;
                case 'yesterday':
                    $range = [ strtotime('yesterday'), strtotime( 'today')];
                    break;
                case 'last week':
                    $range = [ strtotime('last week 00:00:00'),  strtotime('this week 00:00:00')];
                    break;
                case 'last month':
                    $range = [ strtotime('first Day of last month 00:00:00'), strtotime('first Day of this month 00:00:00')];
                    break;
                case 'last year':
                    $range = [ strtotime('last year 1/1'),  strtotime('this year 1/1')];
                    break;
                case 'quarter':
                    $season = ceil((date('n'))/3);//当月是第几季度
                    $range = [mktime(0, 0, 0,$season*3-3+1,1,date('Y')),mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y'))];
                    break;
                default:
                    $op=explode(' - ',$op);
                    $op[0]=strtotime($op[0]);
                    $op[1]=strtotime($op[1]);
                    $range = $op;
            }
        }
        return ['between',$range];
    }


    public function delete_content(){
        $where = Util::getMore([
            ['id',''],
        ],$this->request);
        $map['id']=$where['id'];
        $data['operation_uid']=SystemAdmin::activeAdminIdOrFail();
        $data['operation_identity']=1;
        $res=Report::delete_content($map,$data);
        if($res) {
            return Json::successful('删除成功!');
        }else{
            return Json::fail('删除失败!');
        }
    }

    public function user_delete(){
        $where = Util::getMore([
            ['id',''],
        ],$this->request);
        $map['id']=$where['id'];
        $data['operation_uid']=SystemAdmin::activeAdminIdOrFail();
        $data['operation_identity']=1;
        $res=Report::user_delete($map,$data);
        if($res) {
            return Json::successful('删除成功!');
        }else{
            return Json::fail('删除失败!');
        }
    }

    public function no_deal(){
        $where = Util::getMore([
            ['id',''],
        ],$this->request);
        $map['id']=$where['id'];
        $data['operation_uid']=SystemAdmin::activeAdminIdOrFail();
        $data['operation_identity']=1;
        $res=Report::no_deal($map,$data);
        if($res) {
            return Json::successful('删除成功!');
        }else{
            return Json::fail('删除失败!');
        }
    }

    public function prohibit(){
        return $this->fetch();
    }

    public function prohibit_list(){
        return JsonService::successlayui(Report::getProhibitList());
    }

    public function prohibit_create()
    {
        $params = Util::getMore([
            ['id',''],
            ['num',''],
            ['time_type',''],
            ['sort',''],
        ],$this->request);
        if($params['num']){
            if($params['id']){
                $res=db('report_prohibit')->where(['id'=>$params['id']])->update($params);
            }else{
                $params['status']=1;
                $params['create_time']=time();
                $res=db('report_prohibit')->insert($params);
            }
            if($res){
                return Json::successful('修改成功!');
            }else{
                return Json::fail('修改失败!');
            }
        }else {
            if($params['id']){
                $data=db('report_prohibit')->where(['id'=>$params['id']])->find();
            }else{
                $data['num']=$data['time_type']=$data['sort']=$data['id']='';
            }
            $field = [
                Form::input('num', '时长',$data['num']),
                Form::select('time_type', '时间类型', $data['time_type'])->setOptions([
                    ['label' => '小时', 'value' => 1],
                    ['label' => '天', 'value' => 2],
                ]),
                Form::input('sort', '排序',$data['sort']),
                Form::hidden('id', $data['id']),
            ];
            $form = Form::make_post_form('添加理由', $field, Url::build('prohibit_create'), 2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }
    }

    public function forum_view()
    {
        $params = Util::getMore([
            ['id',''],
        ],$this->request);
        $report=db('report')->where(['id'=>$params['id']])->find();
        if($report['type']==3){
            $forum=db('com_post')->where(['id'=>$report['content']])->find();
        }else{
            $forum=db('com_thread')->where(['id'=>$report['content']])->find();
            $forum['content']=json_decode($forum['content']);
        }

        $this->assign([
            'forum'=>$forum,
        ]);
        return $this->fetch();
    }

    public function choose_prohibit(){
        $params = Util::getMore([
            ['id',''],
            ['choose_data',''],
        ],$this->request);
        if($params['choose_data']!=''){
            $map['id']=$params['id'];
            $data['operation_uid']=SystemAdmin::activeAdminIdOrFail();
            $data['operation_identity']=1;
            $res=Report::user_prohibit($map,$params['choose_data'],$data);
            if($res){
                return Json::successful('禁言成功!');
            }else{
                return Json::fail('禁言失败!');
            }
        }else {
            $value=db('report_prohibit')->where(['status'=>1])->select();
            $choose=[];
            foreach ($value as $key=>$v){
                $v['time_type']=$v['time_type']==1?'小时':'天';
                $choose[]=['label'=>'禁言'.$v['num'].$v['time_type'],'value'=>$v['id']];
            }
//            dump($choose);exit;
            unset($v);
            $field = [
                Form::radio('choose_data', '禁言时间','')->setOptions($choose),
                Form::hidden('id', $params['id']),
            ];
            $form = Form::make_post_form('禁言选择', $field, Url::build('choose_prohibit'), 2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }
    }
}