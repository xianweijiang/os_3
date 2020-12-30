<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */
namespace app\admin\controller\user;
use app\admin\controller\AuthController;
use app\admin\model\com\ForumPower;
use app\admin\model\group\Group;
use app\admin\controller\com\ComTopic;
use app\admin\model\com\ComPost;
use app\admin\model\com\ComThread;
use service\FormBuilder as Form;
use think\Cache;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Request;
use think\Url;
use app\admin\model\user\User as UserModel;
use app\admin\model\user\UserBill AS UserBillAdmin;
use basic\ModelBasic;
use service\HookService;
use behavior\user\UserBehavior;
use app\admin\model\store\StoreVisit;
use app\admin\model\wechat\WechatMessage;
use app\admin\model\order\StoreOrder;
use app\admin\model\store\StoreCouponUser;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use app\admin\model\system\SystemConfig;
use app\osapi\model\common\Support;
use app\admin\model\com\ComTopic as TopicModel;
/**
 * 用户管理控制器
 * Class User
 * @package app\admin\controller\user
 */
class User extends AuthController
{
    use CurdControllerTrait;
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index($uid=''){
        $this->assign('uid',$uid);
        $this->assign('count_user',UserModel::getcount());
        return $this->fetch();
    }
    /**
     * 修改user表状态
     *
     * @return json
     */
    public function set_status($status='',$uid=0,$is_echo=0){
        if($is_echo==0) {
            if ($status == '' || $uid == 0) return Json::fail('参数错误');
            UserModel::where(['uid' => $uid])->update(['status' => $status]);
        }else{
            $uids=Util::postMore([
                ['uids',[]]
            ]);
            UserModel::destrSyatus($uids['uids'],$status);
        }
        return Json::successful($status==0 ? '禁用成功':'解禁成功');
    }
    /**
     * 获取user表
     *
     * @return json
     */
    public function get_user_list($pay_count = '', $post_count = ''){
        $where=Util::getMore([
            ['page',1],
            ['uid',''],
            ['limit',20],
            ['nickname',''],
            ['phone',''],
            ['status',''],
            ['pay_count', $pay_count],
            ['post_count', $post_count],
            ['is_promoter',''],
            ['order',''],
            ['data',''],
            ['user_type',''],
            ['country',''],
            ['province',''],
            ['city',''],
            ['user_time_type',''],
            ['user_time',''],
            ['sex',''],
            ['g_id',''],
        ]);
        $where['is_vest']=0;
        return Json::successlayui(UserModel::getUserList($where));
    }
    /**
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit($uid)
    {
        if(!$uid) return $this->failed('数据不存在');
        $user = UserModel::get($uid);
        $openid=db('user_sync_login')->where('uid',$uid)->value('open_id');
        $unionid=db('user_sync_login')->where('uid',$uid)->value('type_uid');
        $mini_openid=db('user_sync_login')->where('uid',$uid)->value('mini_open_id');
        if(!$user) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('uid','用户编号',$user->getData('uid'))->disabled(1);
        $f[] = Form::input('nickname','用户昵称',$user->getData('nickname'));
        $f[] = Form::frameImageOne('avatar','用户头像',Url::build('admin/widget.images/index',array('fodder'=>'avatar')),$user->getData('avatar'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::input('phone','手机号码',$user->getData('phone'));
        //$f[] = Form::input('real_name','真实姓名',$user->getData('real_name'));
        $f[] = Form::radio('sex','性别',$user->getData('sex'))->options([['value'=>0,'label'=>'保密'],['value'=>1,'label'=>'男'],['value'=>2,'label'=>'女']]);
        //$f[] = Form::date('birthday','生日',$user->getData('birthday'));
        $f[] = Form::input('signature','个人简介',$user->getData('signature'))->type('textarea');
        $f[] = Form::input('mark','备注',$user->getData('mark'))->type('textarea');
        $score=db('system_rule')->where('status',1)->where('is_del',0)->select();
        foreach($score as $value){
            $user_score=UserModel::where('uid',$uid)->value($value['flag']);
            $f[] = Form::number($value['flag'],$value['name'],$user_score)->min(0)->col(8)->readonly(1);
        }
        $f[] = Form::input('open_id','openid',$openid)->readonly(1);
        $f[] = Form::input('type_uid','unionid',$unionid)->readonly(1);
        $f[] = Form::input('mini_open_id','小程序openid',$mini_openid)->readonly(1);
        $f[] = Form::radio('status','状态',$user->getData('status'))->options([['value'=>1,'label'=>'开启'],['value'=>0,'label'=>'禁用']]);
        $sell=db('sell')->where('uid',$uid)->where('status',1)->count();
        $f[] = Form::radio('is_sell','分销权限',$sell)->options([['value'=>1,'label'=>'开启'],['value'=>0,'label'=>'关闭']]);
        $form = Form::make_post_form('用户编辑',$f,Url::build('update',array('id'=>$uid)),5);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function update(Request $request, $uid)
    {
        $data = Util::postMore([
            'nickname',
            'avatar',
            ['phone',''],
            ['avatar',''],
            ['real_name',''],
            ['sex',0],
            ['birthday',''],
            ['signature',''],
            ['mark',''],
            ['is_sell',0],
            ['status',1],
        ],$request);
        if(!$uid) return $this->failed('数据不存在');
        $user = UserModel::get($uid);
        if(!$user) return Json::fail('数据不存在!');
        if(!$data['nickname']) return Json::fail('请输入昵称!');
        $phone=UserModel::Where('uid','neq',$uid)->where('phone',$data['phone'])->find();
        if($phone){
            return Json::fail('该手机号已存在');
        }
        $invite_level=db('invite_level')->where('uid',$uid)->find();
        $sell=db('sell')->where('uid',$uid)->find();
        if($data['is_sell']==1){
            if(!$invite_level && !$sell){
                $data2['uid']=$uid;
                $data2['create_time']=time();
                $data2['audit_time']=time();
                $data2['status']=1;
                $res1=db('sell')->insert($data2);
                if($res1===false){
                    return Json::fail('分销权限修改失败');
                }
            }else{
                if(!$sell){
                    $data2['uid']=$uid;
                    $data2['father1']=$invite_level['father1'];
                    $data2['father2']=$invite_level['father2'];
                    $data2['create_time']=time();
                    $data2['audit_time']=time();
                    $data2['status']=1;
                    $res1=db('sell')->insert($data2);
                    if($res1===false){
                        return Json::fail('分销权限修改失败');
                    }
                }else{
                    $data2['audit_time']=time();
                    $data2['status']=1;
                    $res1=db('sell')->where('uid',$uid)->update($data2);
                    if($res1===false){
                        return Json::fail('分销权限修改失败');
                    }
                }
            }
        }elseif($data['is_sell']==0 && $sell){
            $data2['audit_time']=time();
            $data2['status']=0;
            $res1=db('sell')->where('uid',$uid)->update($data2);
            $set=MessageTemplate::getMessageSet(23);
            if($set['status']==1){
                $message_id=Message::sendMessage($uid,0,$set['template'],1,$set['title'],1,'','my');
                $read_id=MessageRead::createMessageRead($uid,$message_id,$set['popup'],1);
            }
            if($set['sms']==1&&$set['status']==1){
                $account=UserModel::where('uid',$uid)->value('phone');
                $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                $set['template']='【'.$config['cl_sms_sign'].'】'.$set['template'];
                $sms=ChuanglanSmsApi::sendSMS($account,$set['template']); //发送短信
                $sms=json_decode($sms,true);
                if ($sms['code']==0) {
                    $read_data['is_sms']=1;
                    $read_data['sms_time']=time();
                    MessageRead::where('id',$read_id)->update($read_data);
                }
            }
            if($res1===false){
                return Json::fail('分销权限修改失败');
            }
        }
        unset($data['is_sell']);
        $res2 = UserModel::edit($data,$uid);
        if($res2!==false) return Json::successful('修改成功!');
        else return Json::fail('修改失败');
    }
    /**
     * 用户图表
     * @return mixed
     */
    public function user_analysis(){
        $where = Util::getMore([
            ['nickname',''],
            ['status',''],
            ['is_promoter',''],
            ['date',''],
            ['user_type',''],
            ['export',0]
        ],$this->request);
        $user_count=UserModel::consume($where,'',true);
        //头部信息
        $header=[
            [
                'name'=>'新增用户',
                'class'=>'fa-line-chart',
                'value'=>$user_count,
                'color'=>'red'
            ],
            [
                'name'=>'用户留存',
                'class'=>'fa-area-chart',
                'value'=>$this->gethreaderValue(UserModel::consume($where,'',true),$where).'%',
                'color'=>'lazur'
            ],
            [
                'name'=>'新增用户总消费',
                'class'=>'fa-bar-chart',
                'value'=>'￥'.UserModel::consume($where),
                'color'=>'navy'
            ],
            [
                'name'=>'用户活跃度',
                'class'=>'fa-pie-chart',
                'value'=>$this->gethreaderValue(UserModel::consume($where,'',true)).'%',
                'color'=>'yellow'
            ],
        ];
        $name=['新增用户','用户消费'];
        $dates=$this->get_user_index($where,$name);
        $user_index=['name'=>json_encode($name), 'date'=>json_encode($dates['time']), 'series'=>json_encode($dates['series'])];
        //用户浏览分析
        $view=StoreVisit::getVisit($where['date'],['','warning','info','danger']);
        $view_v1=WechatMessage::getViweList($where['date'],['','warning','info','danger']);
        $view=array_merge($view,$view_v1);
        $view_v2=[];
        foreach ($view as $val){
            $view_v2['color'][]='#'.rand(100000,339899);
            $view_v2['name'][]=$val['name'];
            $view_v2['value'][]=$val['value'];
        }
        $view=$view_v2;
        //消费会员排行用户分析
        $user_null=UserModel::getUserSpend($where['date']);
        //消费数据
        $now_number=UserModel::getUserSpend($where['date'],true);
        list($paren_number,$title)=UserModel::getPostNumber($where['date']);
        if($paren_number==0) {
            $rightTitle=[
                'number'=>$now_number>0?$now_number:0,
                'icon'=>'fa-level-up',
                'title'=>$title
            ];
        }else{
            $number=(float)bcsub($now_number,$paren_number,4);
            if($now_number==0){
                $icon='fa-level-down';
            }else{
                $icon=$now_number>$paren_number?'fa-level-up':'fa-level-down';
            }
            $rightTitle=['number'=>$number, 'icon'=>$icon, 'title'=>$title];
        }
        unset($title,$paren_number,$now_number);
        list($paren_user_count,$title)=UserModel::getPostNumber($where['date'],true,'add_time','');
        if($paren_user_count==0){
            $count=$user_count==0?0:$user_count;
            $icon=$user_count==0?'fa-level-down':'fa-level-up';
        }else{
            $count=(float)bcsub($user_count,$paren_user_count,4);
            $icon=$user_count<$paren_user_count?'fa-level-down':'fa-level-up';
        }
        $leftTitle=[
            'count'=>$count,
            'icon'=>$icon,
            'title'=>$title
        ];
        unset($count,$icon,$title);
        $consume=[
            'title'=>'消费金额为￥'.UserModel::consume($where),
            'series'=>UserModel::consume($where,'xiaofei'),
            'rightTitle'=>$rightTitle,
            'leftTitle'=>$leftTitle,
        ];
        $form=UserModel::consume($where,'form');
        $grouping=UserModel::consume($where,'grouping');
        $this->assign(compact('header','user_index','view','user_null','consume','form','grouping','where'));
        return $this->fetch();
    }
    public function gethreaderValue($chart,$where=[]){
        if($where){
            switch($where['date']){
                case null:case 'today':case 'week':case 'year':
                if($where['date']==null){
                    $where['date']='month';
                }
                $sum_user=UserModel::whereTime('add_time',$where['date'])->count();
                if($sum_user==0) return 0;
                $counts=bcdiv($chart,$sum_user,4)*100;
                return $counts;
                break;
                case 'quarter':
                    $quarter=UserModel::getMonth('n');
                    $quarter[0]=strtotime($quarter[0]);
                    $quarter[1]=strtotime($quarter[1]);
                    $sum_user=UserModel::where('add_time','between',$quarter)->count();
                    if($sum_user==0) return 0;
                    $counts=bcdiv($chart,$sum_user,4)*100;
                    return $counts;
                default:
                    //自定义时间
                    $quarter=explode('-',$where['date']);
                    $quarter[0]=strtotime($quarter[0]);
                    $quarter[1]=strtotime($quarter[1]);
                    $sum_user=UserModel::where('add_time','between',$quarter)->count();
                    if($sum_user==0) return 0;
                    $counts=bcdiv($chart,$sum_user,4)*100;
                    return $counts;
                    break;
            }
        }else{
            $num=UserModel::count();
            $chart=$num!=0?bcdiv($chart,$num,5)*100:0;
            return $chart;
        }
    }
    public function get_user_index($where,$name){
        switch ($where['date']){
            case null:
                $days = date("t",strtotime(date('Y-m',time())));
                $dates=[];
                $series=[];
                $times_list=[];
                foreach ($name as $key=>$val){
                    for($i=1;$i<=$days;$i++){
                        if(!in_array($i.'号',$times_list)){
                            array_push($times_list,$i.'号');
                        }
                        $time=$this->gettime(date("Y-m",time()).'-'.$i);
                        if($key==0){
                            $dates['data'][]=UserModel::where('add_time','between',$time)->count();
                        }else if($key==1){
                            $dates['data'][]=UserModel::consume(true,$time);
                        }
                    }
                    $dates['name']=$val;
                    $dates['type']='line';
                    $series[]=$dates;
                    unset($dates);
                }
                return ['time'=>$times_list,'series'=>$series];
            case 'today':
                $dates=[];
                $series=[];
                $times_list=[];
                foreach ($name as $key=>$val){
                    for($i=0;$i<=24;$i++){
                        $strtitle=$i.'点';
                        if(!in_array($strtitle,$times_list)){
                            array_push($times_list,$strtitle);
                        }
                        $time=$this->gettime(date("Y-m-d ",time()).$i);
                        if($key==0){
                            $dates['data'][]=UserModel::where('add_time','between',$time)->count();
                        }else if($key==1){
                            $dates['data'][]=UserModel::consume(true,$time);
                        }
                    }
                    $dates['name']=$val;
                    $dates['type']='line';
                    $series[]=$dates;
                    unset($dates);
                }
                return ['time'=>$times_list,'series'=>$series];
            case "week":
                $dates=[];
                $series=[];
                $times_list=[];
                foreach ($name as $key=>$val){
                    for($i=0;$i<=6;$i++){
                        if(!in_array('星期'.($i+1),$times_list)){
                            array_push($times_list,'星期'.($i+1));
                        }
                        $time=UserModel::getMonth('h',$i);
                        if($key==0){
                            $dates['data'][]=UserModel::where('add_time','between',[strtotime($time[0]),strtotime($time[1])])->count();
                        }else if($key==1){
                            $dates['data'][]=UserModel::consume(true,[strtotime($time[0]),strtotime($time[1])]);
                        }
                    }
                    $dates['name']=$val;
                    $dates['type']='line';
                    $series[]=$dates;
                    unset($dates);
                }
                return ['time'=>$times_list,'series'=>$series];
            case 'year':
                $dates=[];
                $series=[];
                $times_list=[];
                $year=date('Y');
                foreach ($name as $key=>$val){
                    for($i=1;$i<=12;$i++){
                        if(!in_array($i.'月',$times_list)){
                            array_push($times_list,$i.'月');
                        }
                        $t = strtotime($year.'-'.$i.'-01');
                        $arr= explode('/',date('Y-m-01',$t).'/'.date('Y-m-',$t).date('t',$t));
                        if($key==0){
                            $dates['data'][]=UserModel::where('add_time','between',[strtotime($arr[0]),strtotime($arr[1])])->count();
                        }else if($key==1){
                            $dates['data'][]=UserModel::consume(true,[strtotime($arr[0]),strtotime($arr[1])]);
                        }
                    }
                    $dates['name']=$val;
                    $dates['type']='line';
                    $series[]=$dates;
                    unset($dates);
                }
                return ['time'=>$times_list,'series'=>$series];
            case 'quarter':
                $dates=[];
                $series=[];
                $times_list=[];
                foreach ($name as $key=>$val){
                    for($i=1;$i<=4;$i++){
                        $arr=$this->gettime('quarter',$i);
                        if(!in_array(implode('--',$arr).'季度',$times_list)){
                            array_push($times_list,implode('--',$arr).'季度');
                        }
                        if($key==0){
                            $dates['data'][]=UserModel::where('add_time','between',[strtotime($arr[0]),strtotime($arr[1])])->count();
                        }else if($key==1){
                            $dates['data'][]=UserModel::consume(true,[strtotime($arr[0]),strtotime($arr[1])]);
                        }
                    }
                    $dates['name']=$val;
                    $dates['type']='line';
                    $series[]=$dates;
                    unset($dates);
                }
                return ['time'=>$times_list,'series'=>$series];
            default:
                $list=UserModel::consume($where,'default');
                $dates=[];
                $series=[];
                $times_list=[];
                foreach ($name as $k=>$v){
                    foreach ($list as $val){
                        $date=$val['add_time'];
                        if(!in_array($date,$times_list)){
                            array_push($times_list,$date);
                        }
                        if($k==0){
                            $dates['data'][]=$val['num'];
                        }else if($k==1){
                            $dates['data'][]=UserBillAdmin::where(['uid'=>$val['uid'],'type'=>'pay_product'])->sum('number');
                        }
                    }
                    $dates['name']=$v;
                    $dates['type']='line';
                    $series[]=$dates;
                    unset($dates);
                }
                return ['time'=>$times_list,'series'=>$series];
        }
    }
    public function gettime($time='',$season=''){
        if(!empty($time) && empty($season)){
            $timestamp0 = strtotime($time);
            $timestamp24 =strtotime($time)+86400;
            return [$timestamp0,$timestamp24];
        }else if(!empty($time) && !empty($season)){
            $firstday=date('Y-m-01',mktime(0,0,0,($season - 1) *3 +1,1,date('Y')));
            $lastday=date('Y-m-t',mktime(0,0,0,$season * 3,1,date('Y')));
            return [$firstday,$lastday];
        }
    }

    /**
     * 会员等级首页
     */
    public function group(){
        return $this->fetch();
    }
    /**
     * 会员详情
     */
    public function see($uid=''){
        $this->assign([
            'uid'=>$uid,
            'userinfo'=>UserModel::getUserDetailed($uid),
            'is_layui'=>true,
            'headerList'=>UserModel::getHeaderList($uid),
            'count'=>UserModel::getCountInfo($uid),
        ]);
        return $this->fetch();
    }
    /*
     * 获取某个用户的推广下线
     * */
    public function getSpreadList($uid,$page=1,$limit=20){
        return Json::successful(UserModel::getSpreadList($uid,(int)$page,(int)$limit));
    }
    /**
     * 获取某用户的订单列表
     */
    public function getOneorderList($uid,$page=1,$limit=20){
        return Json::successful(StoreOrder::getOneorderList(compact('uid','page','limit')));
    }
    /**
     * 获取某用户的积分列表
     */
    public function getOneIntegralList($uid,$page=1,$limit=20){
        return Json::successful(UserBillAdmin::getOneIntegralList(compact('uid','page','limit')));
    }
    /**
     * 获取某用户的积分列表
     */
    public function getOneSignList($uid,$page=1,$limit=20){
        return Json::successful(UserBillAdmin::getOneSignList(compact('uid','page','limit')));
    }
    /**
     * 获取某用户的持有优惠劵
     */
    public function getOneCouponsList($uid,$page=1,$limit=20){
        return Json::successful(StoreCouponUser::getOneCouponsList(compact('uid','page','limit')));
    }
    /**
     * 获取某用户的余额变动记录
     */
    public function getOneBalanceChangList($uid,$page=1,$limit=20){
        return Json::successful(UserBillAdmin::getOneBalanceChangList(compact('uid','page','limit')));
    }

    public function edit_score($uids)
    {
        $score=db('system_rule')->where('status',1)->where('is_del',0)->select();
        $this->assign('uids',$uids);
        $this->assign('score',$score);
        return $this->fetch();
    }

    public function update_score(){
        $post=Util::postMore([
            'uids',
            'type',
            'content',
        ]);
        $post['score']=$_POST['score'];
        if(empty($post['uids'])){
            return Json::fail('请选择需要调整积分的用户');
        }
        if($post['score'][0]['value']<=0||empty($post['score'][0]['value'])){
            return Json::fail('请输入正确的分值');
        }
        if(empty($post['content'])){
            return Json::fail('理由不能为空');
        }
        $post['uids'] = explode(',',$post['uids']);
        foreach ($post['uids'] as $val){
            $template='';
            if($post['type']==1){
                $log = [] ;
                foreach ($post['score'] as &$value){
                    $value['name']=db('system_rule')->where('flag',$value['flag'])->value('name');
                    UserModel::where('uid',$val)->setInc($value['flag'],$value['value']);
                    $template=$template.$value['name'].':'.$value['value'].'；';
                    $log[$value['flag']] = $value['value'];
                }
                unset($value);
                Support::jiafenlog($val,'系统赠送积分',$log,$post['type'],'行为');
                $score_type='加分';
            }else{
                $log = [] ;
                foreach ($post['score'] as &$value){
                    $value['name']=db('system_rule')->where('flag',$value['flag'])->value('name');
                    UserModel::where('uid',$val)->setDec($value['flag'],$value['value']);
                    $template=$template.$value['name'].':'.$value['value'].'；';
                    $log[$value['flag']] = $value['value'];
                }
                unset($value);
                Support::jiafenlog($val,'系统扣除积分',$log,$post['type'],'行为');
                $score_type='减分';
            }
            $time=time_format(time());
            $set=MessageTemplate::getMessageSet(41);
            $template=str_replace('{分值}', $template, $set['template']);
            $template=str_replace('{年月日时分}',$time,$template);
            $template=str_replace('{改分理由}', $post['content'], $template);
            $template=str_replace('{加减分}', $score_type, $template);
            if($set['status']==1){
                $message_id=Message::sendMessage($val,0,$template,1,$set['title'],1,'','score_log');
                MessageRead::createMessageRead($val,$message_id,$set['popup'],1);
            }
        }
        return Json::successful('修改成功!');
    }

    /**
     * 一键清空用户
     */
    public function clear_user(){
        $post=Util::postMore([
            ['uid']
        ]);
        if(empty($post['uid'])){
            return JsonService::fail('请选择需要清空的用户');
        }else{
            $res1=ComThread::where('author_uid',$post['uid'])->update(['status'=>-1]);
            $res2=ComPost::where('author_uid',$post['uid'])->where('is_thread',1)->update(['status'=>-1]);
            $res3=ComPost::where('author_uid',$post['uid'])->where('is_thread',0)->update(['content'=>'该内容已删除']);
            $res4=TopicModel::where('uid',$post['uid'])->update(['status'=>-1]);
            if($res1!==false&&$res2!==false&&$res3!==false&&$res4!==false){
                return Json::successful('成功');
            }else{
                return Json::fail('失败');
            }

        }
    }

 /**
     * 绑定用户组(批量处理)
     * @param Request $request
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.30
     */
    public function add_group_uid(Request $request){
        $post = Util::postMore([
            ['ids', ''],
            ['is_post', 0],
            ['user',[]],
            ['time',[]],
        ], $request);
        if ($post['is_post'] == 1) {
            $res = Group::bind_group_uid($post['ids'],$post['user'],$post['time'],false);
            if($res!==false){
                return Json::successful('修改成功!');
            }else{
                return Json::fail('修改失败!');
            }
        } else {
            $map['type']=['in',[1,6]];
            $map['status']=1;
            $map['id']=['not in',[3,4]];
            $post = Util::getMore([
                ['ids', ''],
            ], $request);
            $group=Group::where($map)->select();
            $this->assign(
                [
                    'ids' => $post['ids'],
                    'group'=>$group,
                ]
            );
            return $this->fetch('add_group_uid');
        }
    }
    /**
     * 绑定用户组(单个处理)
     * @param Request $request
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.30
     */
    public function see_group_uid(Request $request){
        $post = Util::postMore([
            ['ids', ''],
            ['is_post', 0],
            ['user',[]],
            ['time',[]],
        ], $request);
        if ($post['is_post'] == 1) {
            $res = Group::bind_group_uid($post['ids'],$post['user'],$post['time'],true);
            if($res){
                Cache::clear('group_by_uid');
                ForumPower::clear_cache();
                return Json::successful('修改成功!');
            }else{
                return Json::fail('修改失败!');
            }
        } else {
            $map['type']=['in',[1,6]];
            $map['status']=1;
            $post = Util::getMore([
                ['uid', ''],
            ], $request);
            $group=Group::where($map)->select();
            //已经获取的
            $group_uid=Group::get_bind_group_uid($post['uid']);
            $system_group=Group::get_system_group($post['uid']);
            $level_group=Group::get_level_group($post['uid']);
            $cert_group=Group::get_cert_group($post['uid']);
            $this->assign(
                [
                    'ids' => $post['uid'],
                    'group'=>$group,
                    'group_uid'=>$group_uid,
                    'count'=>count($group_uid),
                    'system_group'=>$system_group,
                    'level_group'=>$level_group,
                    'cert_group'=>$cert_group,
                ]
            );
            return $this->fetch('see_group_uid');
        }
    }
    /**
     * 添加扩展用户组
     * @param Request $request
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.30
     */
    public function add_user_group(Request $request){
        $post = Util::postMore([
            ['count', 1],
        ], $request);
        $map['type']=['in',[1,6]];
        $map['status']=1;
        $map['id']=['not in',[3,4]];
        $group=Group::where($map)->select();
        $this->assign(
            [
                'num' => $post['count']+1,
                'group'=>$group,
            ]
        );
        $data['count']=$post['count']+1;
        $data['html']=$this->fetch('_add_user_group');
        return Json::successful($data);
    }


    /**
     * 用户group
     *
     * @return \think\Response
     */
    public  function group_uid_index($g_id=''){
        $this->assign('g_id',$g_id);
//        $this->assign('count_user',UserModel::getcount());
        return $this->fetch();
    }
}
