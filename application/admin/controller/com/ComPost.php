<?php
namespace app\admin\controller\com;

use app\admin\controller\AuthController;
use app\admin\model\com\ComPost as PostModel;
use app\admin\model\com\ComThread as ThreadModel;
use app\admin\model\com\ComForum as ForumModel;
use app\admin\model\user\User as UserModel;
use app\commonapi\model\Gong;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use think\Url;
use app\admin\model\system\SystemAttachment;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use app\admin\model\system\SystemConfig;

/**
 * 评论控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class ComPost extends AuthController
{
	public function index(){
	    $status=osx_input('status','');
        $name=osx_input('name','');
        $tid=osx_input('tid','');
        $is_vest=osx_input('is_vest',0,'intval');
		$this->assign([
			'year'   => getMonth('y'),
			'status' => $status,
			'cate'   => ForumModel::getCatTierList(),
			'name'   => $name,
            'tid'   => $tid,
            'is_vest'=>$is_vest
		]);
		return $this->fetch();
	}


	public function post_list(){
		$where = Util::getMore([
            ['status',''],
            ['type',''],
            ['name',''],
			['fid',''],
            ['uid', ''],
			['data'    , ''],
            ['type', ''],
            ['content', ''],
            ['order',''],
            ['tid',''],
            ['page',1],
            ['limit',20],
            ['excel',0],
            ['is_vest',0]
        ]);
        return JsonService::successlayui(PostModel::PostList($where));
	}

	/**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $type=osx_input('type','');
        $show_type=osx_input('show_type','');
        $field = [
            Form::input('name','标题')->col(Form::col(24)),
            Form::input('url','链接'),
            Form::frameImageOne('pic','图片',Url::build('admin/widget.images/index',array('fodder'=>'pic')))->icon('image')->width('100%')->height('500px'),
            Form::number('sort','排序')->col(8),
            Form::radio('status','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
            Form::hidden('type', $type),
            Form::hidden('show_type', $show_type),
        ];
        $form = Form::make_post_form('创建评论',$field, Url::build('save'),2);
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
            'name',
            'url',
            'type',
            'icon',
            'sort',
            'status'
        ],$request);
        if(!$data['name']) return Json::fail('请输入评论名称');
        PostModel::set($data);
        return Json::successful('创建评论成功!');
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update(Request $request)
    {
        $id=osx_input('post.id',0,'intval');
        $data = Util::postMore([
            'name',
            'url',
            'type',
            'icon',
            'sort',
            'status',
        ],$request);
        if(!$data['name']) return Json::fail('请输入评论名称');
        PostModel::edit($data,$id);
        return Json::successful('修改成功!');
    }

   /**
     * 显示编辑资源表单页.
     * @return \think\Response
     */
    public function edit()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $data = PostModel::get($id);
        if(!$data) return Json::fail('数据不存在!');

        $field = [
            Form::input('name','标题', $data['name'])->col(Form::col(24)),
            Form::input('url','链接', $data['url']),
            Form::frameImageOne('pic','图标',Url::build('admin/widget.images/index',array('fodder'=>'pic')), $data['pic'])->icon('image')->width('100%')->height('500px'),
            Form::number('sort','排序', $data['sort'])->col(8),
            Form::radio('status','状态', $data['sort'])->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
            Form::hidden('type', $data['type']),
            Form::hidden('show_type', $data['show_type']),
        ];
        $form = Form::make_post_form('编辑评论',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function view(){
        $id=osx_input('id',0,'intval');
    	if(!$id) return $this->failed('数据不存在');
        $data = PostModel::get($id);
        if(!$data) return Json::fail('数据不存在!');
        $field = [
            Form::textarea('content','文章内容', emoji_decode($data['content']))->readonly(true),
        ];
        $form = Form::make_post_form('详情',$field,Url::build('update',array('id'=>$id)),2);
        $form->hiddenSubmitBtn(true);
        $form->hiddenResetBtn(true);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 快速编辑
     *
     * @return json
     */
    public function quick_edit(){
        $id=osx_input('id',0,'intval');
        $field=osx_input('field','','text');
        $value=osx_input('value','','text');
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(PostModel::where(['id'=>$id])->update([$field=>$value]) !== false){
            if($value==-1 && $field=='status'){
                PostModel::where(['id'=>$id])->update(['del_time'=>time()]);
                $post=PostModel::where(['id'=>$id])->find();
                ThreadModel::where('id',$post['tid'])->setDec('reply_count');
                PostModel::where('id',$post['to_reply_id'])->setDec('comment_count');
                $set=MessageTemplate::getMessageSet(34);
                $time=time_format(time());
                $title = ThreadModel::where('id',$post['tid'])->value('title');
                if(!$title){
                    $title=db('com_thread')->where('id', $post['tid'])->value('content');
                    $title=json_decode($title,true);
                }
                $length_title=mb_strlen($title,'UTF-8');
                $length_content=mb_strlen($post['content'],'UTF-8');
                if($length_title>7){
                    $title=mb_substr($title,0,7,'UTF-8').'…';
                }
                if($length_content>7){
                    $post['content']=mb_substr($post['content'],0,4,'UTF-8').'…';
                }
                $template=str_replace('{年月日时分}', $time, $set['template']);
                $template=str_replace('{评论内容}', $post['content'], $template);
                $template=str_replace('{帖子标题}', $title, $template);
                if($set['status']==1){
                    $message_id=Message::sendMessage($post['author_uid'],0,$template,1,$set['title'],1);
                    $read_id=MessageRead::createMessageRead($post['author_uid'],$message_id,$set['popup'],1);
                }
                if($set['sms']==1&&$set['status']==1){
                    $account=UserModel::where('uid',$post['author_uid'])->value('phone');
                    $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                    $template='【'.$config['cl_sms_sign'].'】'.$template;
                    $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                    $sms=json_decode($sms,true);
                    if ($sms['code']==0) {
                        $read_data['is_sms']=1;
                        $read_data['sms_time']=time();
                        MessageRead::where('id',$read_id)->update($read_data);
                    }
                }
                //减分
               Gong::delaction('beishanpinglun',$post['author_uid']) ;

                website_connect_notify($post['author_uid'],$id,$post['author_uid'],'admin_com_post_delete');//通知第三方平台，任务回调
            }
            return JsonService::successful('保存成功');
        } else{
            return JsonService::fail('保存失败');
        }


    }

 /**
     * 批量删除评论
     *
     * @return json
     */
    public function delete(){
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要删除的评论');
        }else{
            $res = PostModel::where('id','in',$post['ids'])->update(['status'=>-1,'del_time'=>time()]);
            $post=PostModel::where('id','in',$post['ids'])->select();
            $set=MessageTemplate::getMessageSet(34);
            foreach($post as &$value){
                ThreadModel::where('id',$value['tid'])->setDec('reply_count');
                PostModel::where('id',$value['to_reply_id'])->setDec('comment_count');
                $time=time_format(time());
                $title = ThreadModel::where('id',$value['tid'])->value('title');
                $length_title=mb_strlen($title,'UTF-8');
                $length_content=mb_strlen($value['content'],'UTF-8');
                if($length_title>7){
                    $title=mb_substr($title,0,7,'UTF-8').'…';
                }
                if($length_content>7){
                    $value['content']=mb_substr($value['content'],0,4,'UTF-8').'…';
                }
                $template=str_replace('{年月日时分}', $time, $set['template']);
                $template=str_replace('{评论内容}', $value['content'], $template);
                $template=str_replace('{帖子标题}', $title, $template);
                if($set['status']==1){
                    $message_id=Message::sendMessage($value['author_uid'],0,$template,1,$set['title'],1);
                    $read_id=MessageRead::createMessageRead($value['author_uid'],$message_id,$set['popup'],1);
                }
                if($set['sms']==1&&$set['status']==1){
                    $account=UserModel::where('uid',$value['author_uid'])->value('phone');
                    $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                    $template='【'.$config['cl_sms_sign'].'】'.$template;
                    $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                    $sms=json_decode($sms,true);
                    if ($sms['code']==0) {
                        $read_data['is_sms']=1;
                        $read_data['sms_time']=time();
                        MessageRead::where('id',$read_id)->update($read_data);
                    }
                }
            }
            unset($value);
            if($res)
                return JsonService::successful('删除成功');
            else
                return JsonService::fail('删除失败');
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function remove()
    {
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要清理的评论');
        }else{
            $res = PostModel::destroy($post['ids']);
            if($res)
                return JsonService::successful('清理成功');
            else
                return JsonService::fail('清理失败');
        }
    }

     /**
     * 批量还原评论
     *
     * @return json
     */
    public function restore(){
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要还原的评论');
        }else{
            $res = PostModel::where('id','in',$post['ids'])->update(['status'=>1]);
            if($res)
                return JsonService::successful('还原成功');
            else
                return JsonService::fail('还原失败');
        }
    }

}