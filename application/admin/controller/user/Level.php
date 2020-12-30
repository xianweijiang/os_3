<?php
namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use app\admin\model\group\Group;
use app\admin\model\system\SystemUserGrade;
use app\admin\model\system\SystemGradeDesc;
use app\admin\model\system\SystemUserTask;
use service\FormBuilder as Form;
use service\JsonService;

use service\UtilService;
use think\Url;
use traits\CurdControllerTrait;
use think\Request;

/**
 * 会员设置
 * Class UserLevel
 * @package app\admin\controller\user
 */
class Level extends AuthController
{
    use CurdControllerTrait;

    /*
     * 等级展示
     * */
    public function index($type)
    {
        $this->assign(compact('type')) ;
        return $this->fetch();
    }

    /*
     * 创建form表单
     * */
    public function create($id=0,$type)
    {

        if($id) $vipinfo=SystemUserGrade::get($id);
        $field[]= Form::hidden('type',$type);
        $field[]= Form::input('name','等级名称',isset($vipinfo) ? $vipinfo->name : '')->col(Form::col(24));
        $field[]= Form::number('experience','经验值上限',isset($vipinfo) ? $vipinfo->experience : 0)->min(0)->col(24);
        $field[]= Form::frameImageOne('icon','图标150*75',Url::build('admin/widget.images/index',array('fodder'=>'icon')),isset($vipinfo) ? $vipinfo->icon : '')->icon('image')->width('100px')->height('500px');
        $field[]= Form::frameImageOne('image','大图标300*150',Url::build('admin/widget.images/index',array('fodder'=>'image')),isset($vipinfo) ? $vipinfo->image : '')->icon('image')->width('100%')->height('500px');
        $field[]= Form::textarea('explain','等级说明',isset($vipinfo) ? $vipinfo->explain : '');
        $title = empty($id) ? '添加等级' : '编辑等级' ;
        $form = Form::make_post_form($title,$field,Url::build('save',['id'=>$id]),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /*
     * 会员等级添加或者修改
     * @param $id 修改的等级id
     * @return json
     * */
    public function save($id=0)
    {
        $data=UtilService::postMore([
            ['name',''],
            ['experience',0],
            ['icon',''],
            ['image',''],
            ['explain',''],
            ['type',0],
        ]);

        if(!$data['name']) return JsonService::fail('请输入等级名称');
        if(!$data['experience']) return JsonService::fail('请输入经验值上限');
        if(!$data['icon']) return JsonService::fail('请上传等级小图标');
        if(!$data['image']) return JsonService::fail('请上传等级大图标');
        if(!$data['explain']) return JsonService::fail('请输入等级说明');
        SystemUserGrade::beginTrans();
        try{
            //修改
            if($id){
                $res = SystemUserGrade::where('id',$id)->update($data);
                if($res!==false){
                    SystemUserGrade::commitTrans();
                    return JsonService::successful('修改成功');
                }else{
                    SystemUserGrade::rollbackTrans();
                    return JsonService::fail('修改失败');
                }
            }else{
                //新增
                $data['add_time']=time();
                $id=SystemUserGrade::insertGetId($data);

                if($id){
                    //晋级用户组添加
                    $value['type']=3;
                    $value['name']=$data['name'];
                    $value['bind_condition']=$id;
                    $value['remark']=$data['explain'];
                    $res=Group::add_group_new($value);
                    if(!$res){
                        SystemUserGrade::rollbackTrans();
                        return JsonService::successful('添加失败');
                    }
                    //晋级用户组添加end
                    SystemUserGrade::commitTrans();
                    return JsonService::successful('添加成功');
                }else{
                    SystemUserGrade::rollbackTrans();
                    return JsonService::fail('添加失败');
                }
            }
        }catch (\Exception $e){
            SystemUserGrade::rollbackTrans();
            return JsonService::fail($e->getMessage());
        }
    }
    /*
     * 获取系统设置的vip列表
     * @param int page
     * @param int limit
     * */
    public function get_system_vip_list($type)
    {
        $where=UtilService::getMore([
            ['page',0],
            ['limit',10],

        ]);
        return JsonService::successlayui(SystemUserGrade::getSytemList($where,$type));
    }

    /*
     * 删除会员等级
     * @param int $id
     * */
    public function delete($id=0)
    {
        if(SystemUserGrade::edit(['is_del'=>1],$id))

            return JsonService::successful('删除成功');
        else
            return JsonService::fail('删除失败');
    }

    /**
     * 设置单个产品上架|下架
     *
     * @return json
     */
    public function set_show($is_show='',$id=''){
        ($is_show=='' || $id=='') && Json::fail('缺少参数');
        $res=SystemUserGrade::where(['id'=>$id])->update(['is_show'=>(int)$is_show]);
        if($res){
            return JsonService::successful($is_show==1 ? '显示成功':'隐藏成功');
        }else{
            return JsonService::fail($is_show==1 ? '显示失败':'隐藏失败');
        }
    }

    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_value($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && Json::fail('缺少参数');
        if(SystemUserGrade::where(['id'=>$id])->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }


    /*
     * 等级任务列表
     * @param int $vip_id 等级id
     * @return json
     * */
    public function tash($level_id=0)
    {
        $this->assign('level_id',$level_id);
        return $this->fetch();
    }

    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_tash_value($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && Json::fail('缺少参数');
        if(SystemUserTask::where(['id'=>$id])->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }

    /**
     * 设置单个产品上架|下架
     *
     * @return json
     */
    public function set_tash_show($is_show='',$id=''){
        ($is_show=='' || $id=='') && Json::fail('缺少参数');
        $res=SystemUserTask::where(['id'=>$id])->update(['is_show'=>(int)$is_show]);
        if($res){
            return JsonService::successful($is_show==1 ? '显示成功':'隐藏成功');
        }else{
            return JsonService::fail($is_show==1 ? '显示失败':'隐藏失败');
        }
    }

    /**
     * 设置单个产品上架|下架
     *
     * @return json
     */
    public function set_tash_must($is_must='',$id=''){
        ($is_must=='' || $id=='') && Json::fail('缺少参数');
        $res=SystemUserTask::where(['id'=>$id])->update(['is_must'=>(int)$is_must]);
        if($res){
            return JsonService::successful('设置成功');
        }else{
            return JsonService::fail('设置失败');
        }
    }

    /*
     * 生成任务表单
     * @param int $id 任务id
     * @param int $vip_id 会员id
     * @return html
     * */
    public function create_tash($id=0,$level_id=0)
    {
        if($id) $tash=SystemUserTask::get($id);
        $field[]= Form::select('task_type','任务类型',isset($tash) ? $tash->task_type : '')->setOptions(function(){
            $list = SystemUserTask::getTaskTypeAll();
            $menus=[];
            foreach ($list as $menu){
                $menus[] = ['value'=>$menu['type'],'label'=>$menu['name'].'----单位['.$menu['unit'].']'];
            }
            return $menus;
        })->filterable(1);
        $field[]= Form::number('number','限定数量',isset($tash) ? $tash->number : 0)->min(0)->col(24);
        $field[]= Form::number('sort','排序',isset($tash) ? $tash->sort : 0)->min(0)->col(24);
        $field[]= Form::radio('is_show','是否显示',isset($tash) ? $tash->is_show : 1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])->col(24);
        $field[]= Form::radio('is_must','是否务必达成',isset($tash) ? $tash->is_must : 1)->options([['label'=>'务必达成','value'=>1],['label'=>'完成其一','value'=>0]])->col(24);
        $field[]= Form::textarea('illustrate','任务说明',isset($tash) ? $tash->illustrate : '');
        $form = Form::make_post_form('添加任务',$field,Url::build('save_tash',['id'=>$id,'level_id'=>$level_id]),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }



    /*
     * 保存或者修改任务
     * @param int $id 任务id
     * @param int $vip_id 会员id
     * */
    public function save_tash($id=0,$level_id=0)
    {
        if(!$level_id) return JsonService::fail('缺少参数');
        $data=UtilService::postMore([
            ['task_type',''],
            ['number',0],
            ['is_show',0],
            ['sort',0],
            ['is_must',0],
            ['illustrate',''],
        ]);
        if(!$data['task_type']) return JsonService::fail('请选择任务类型');
        if($data['number'] < 0) return JsonService::fail('请输入限定数量');
        $tash=SystemUserTask::getTaskType($data['task_type']);
        if($tash['max_number']!=0 && $data['number'] > $tash['max_number']) return JsonService::fail('您设置的限定数量超出最大限制,最大限制为:'.$tash['max_number']);
        $data['name']=SystemUserTask::setTaskName($data['task_type'],$data['number']);
        try{
            if($id){
                SystemUserTask::edit($data,$id);
                return JsonService::successful('修改成功');
            }else{
                $data['level_id']=$level_id;
                $data['add_time']=time();
                $data['real_name']=$tash['real_name'];
                if(SystemUserTask::set($data))
                    return JsonService::successful('添加成功');
                else
                    return JsonService::fail('添加失败');
            }
        }catch (\Exception $e){
            return JsonService::fail($e->getMessage());
        }
    }

    /*
     * 异步获取等级任务列表
     * @param int $vip_id 会员id
     * @param int $page 分页
     * @param int $limit 显示条数
     * @return json
     * */
    public function get_tash_list($level_id=0)
    {
        list($page,$limit)=UtilService::getMore([
            ['page',1],
            ['limit',10],
        ],$this->request,true);
        return JsonService::successlayui(SystemUserTask::getTashList($level_id,(int)$page,(int)$limit));
    }

    /*
     * 删除任务
     * @param int 任务id
     * */
    public function delete_tash($id=0)
    {
        if(!$id) return JsonService::fail('缺少参数');
        if(SystemUserTask::del($id))
            return JsonService::successful('删除成功');
        else
            return JsonService::fail('删除失败');
    }

    public function edit_content(Request $request){
        $id = 1 ;
        $type = $request->param()['type'] ;
        $content = empty($id) ? '' : SystemGradeDesc::where('type',$type)->find() ;

        $this->assign([
            'content'=> empty($content['description']) ? '': $content['description'],
            'field'=>'description',
            'action'=>Url::build('change_field',['id'=>empty($content['id']) ? 0 : $content['id'] ,'field'=>'description','type'=>$type])
        ]);
        return $this->fetch('public/edit_content');
    }

    public function change_field(){

        $data['description'] = osx_input('description','','html');
        $data['type'] = osx_input('type');
        $id = osx_input('id',0,'intval');

        if(empty($id)){
            $res = SystemGradeDesc::set($data);
        }else{
            $res = SystemGradeDesc::edit($data,$id);
        }

        if($res)
            return JsonService::successful('添加成功');
        else
            return JsonService::fail('添加失败');
    }

}