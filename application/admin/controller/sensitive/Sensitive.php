<?php

namespace app\admin\controller\sensitive;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use app\admin\model\sensitive\Sensitive as SensitiveModel;
use think\Request;
use think\Url;
use think\Csv;
use think\Db;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class Sensitive extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->fetch();
    }

    public function delete($id=''){
        if($id==''){
            JsonService::fail('缺少参数');
        }
        $res=SensitiveModel::where('id',$id)->delete();
        if($res){
            return JsonService::successful('删除成功');
        }else{
            return JsonService::fail('删除失败');
        }
    }

    /**
     * 栏目设置列表
     *
     * @return json
     */
    public function sensitive_list(){
        $where=Util::getMore([
            ['sensitive',''],
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(SensitiveModel::sensitiveList($where));
    }

    public function create()
    {
        $field = [
            Form::input('sensitive','敏感词')->type('textarea'),
            Form::select('level','类别',1)->setOptions(function(){
                $menus=[['value'=>1,'label'=>'替换'],
                    //['value'=>2,'label'=>'删除'],
                    //['value'=>3,'label'=>'审核']
                ];
                return $menus;
            })->filterable(1),
        ];
        $form = Form::make_post_form('添加敏感词',$field,Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function save(Request $request)
    {
        $data = Util::postMore([
            'sensitive',
            'level',
        ],$request);
        $data['status']=1;
        $data['create_time']=time();
        SensitiveModel::set($data);
        return Json::successful('成功');
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'sensitive',
            'level',
        ],$request);
        SensitiveModel::editSensitive($data,$id);
        return Json::successful('成功');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $sensitive = SensitiveModel::getOne($id);
        if(!$sensitive) return Json::fail('数据不存在!');
        $field = [
            Form::input('sensitive','敏感词',$sensitive['sensitive'])->type('textarea'),
            Form::select('level','类别',(string)$sensitive['level'])->setOptions(function(){
                $menus=[['value'=>1,'label'=>'替换'],
                    //['value'=>2,'label'=>'删除'],
                    //['value'=>3,'label'=>'审核']
                ];
                return $menus;
            })->filterable(1),
        ];
        $form = Form::make_post_form('编辑栏目',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function set_on($is_on='',$id=''){
        ($is_on=='' || $id=='') && JsonService::fail('缺少参数');
        $res=ColumnModel::where(['id'=>$id])->update(['status'=>(int)$is_on]);
        if($res){
            return JsonService::successful($is_on==1 ? '启用成功':'禁用成功');
        }else{
            return JsonService::fail($is_on==1 ? '启用失败':'禁用失败');
        }
    }

    public function set_column($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(ColumnModel::where(['id'=>$id])->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }


    /*
     * CSV导入
     */
    public function upSensitive()
    {
        // 获取表单上传文件
        $file = request()->file('examfile');
        if(empty($file)) {
            $this->error('请选择上传文件');
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH.'public'.DS.'upload');
        //获取文件（日期/文件），$info->getFilename();
        $filename = ROOT_PATH.'public'.DS.'upload/'.$info->getSaveName();
        $handle = fopen($filename,'r');
        $csv = new Csv();
        $result = $csv->input_csv($handle); // 解析csv
        $len_result = count($result);
        if($len_result == 0){
            return JsonService::fail('此文件中没有数据！');
        }
        $data_values = '';
        for($i = 1;$i < $len_result+1;$i ++) { // 循环获取各字段值
            $arr = array_values($result[$i]);
            $sensitive = iconv('GBK','utf-8',$arr[0] ); // 中文转码
            $level = $arr[1];
            $create_time = time();
            $status = 1;
            $data_values .= "('$sensitive','$level','$create_time','$status'),";
        }
        $data_values = substr($data_values,0,- 1 ); // 去掉最后一个逗号
        fclose($handle); // 关闭指针
        $table=config('database.prefix').'sensitive';
        // 批量插入数据表中
        $result = DB::execute("insert into `$table` (`sensitive`,`level`,`create_time`,`status`) values $data_values" );
        if($result){
            return JsonService::successful('文件上传成功，数据已经导入！');
        }else{
            // 上传失败获取错误信息
            return JsonService::fail($file->getError());
        }
    }

}
