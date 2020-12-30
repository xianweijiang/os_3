<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-25 16:42:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-16 16:45:36
    cd /Users/shilc/dnmp/apidoc/ && npm run build-certification
 */

namespace app\commonapi\controller;

use app\admin\model\system\SystemConfig;
use think\Request;
use service\UtilService;
use service\JsonService;
use basic\ControllerBasic;
use app\commonapi\model\certification\CertificationCate as CateModel;
use app\commonapi\model\certification\CertificationType as typeModel;
use app\commonapi\model\certification\CertificationDatum as DatumModel;
use app\commonapi\model\certification\CertificationEntity as EntityModel;
use app\commonapi\model\certification\CertificationFaq as FaqModel;

class Certification extends ControllerBasic
{
    /**
     * @api      {get} /commonapi/Certification/cate_list.html 类别列表
     * @apiGroup certification
     * @apiName  cate_list
     * @apiParam {Number} page 页码
     * @apiParam {Number} page_num 每页数量
     * @apiParam {json} info
     * @apiParamExample {json} Request-Example:
     * {"page":"1","page_num":"20"}
     * @apiSuccess {json} result
     * @apiSuccess {Number} data.status 认证状态 snull 去认证；0 审核中；1 已认证 -1 已驳回；
     * @apiSuccess {Number} data.entity_id 认证id 已经认证过的有此id；
     * @apiSuccessExample {json} Success-Response:
     * {"code":200,"msg":"ok","data":[{"name":"\u6d4b\u8bd5\u4e2a\u4eba\u8ba4\u8bc1","desc":"\u6d4b\u8bd5\u4e2a\u4eba\u8ba4\u8bc1","icon":"http:\/\/shop.com\/public\/uploads\/attach\/2019\/03\/28\/5c9ccca1c78cd.gif","id":1,"status":1}],"count":0}
     */
    public function cate_list()
    {
        $uid=osx_input('get.uid',0,'intval');
        if(!$uid){
            $uid=get_uid();
        }
        $page=osx_input('get.page',1,'intval');
        $page_num=osx_input('get.page_num',20,'intval');

        $list = CateModel::getApiPage(['page'=>$page,'page_num'=>$page_num,'uid'=>$uid]);
        $this->apiSuccess($list);
    }
    /**
     * @api      {get} /commonapi/Certification/cate/1.html 认证详情
     * @apiGroup certification
     * @apiName  cate
     * @apiParam {Number} id 认证类别id
     * @apiParam {json} info
     * @apiSuccess {json} result
     * @apiSuccess {Number} data.satisfy_status 是否可申请 false 不能申请；true 可以申请
     * @apiSuccess {Number} data.cateconditions 申请条件
     * @apiSuccess {Number} data.cateconditions.satisfy  申请条件满足情况
     * @apiSuccess {Number} data.cateprivileges 认证特权
     * @apiSuccessExample {json} Success-Response:
     * {"code":200,"msg":"ok","data":{"name":"\u6d4b\u8bd5\u4e2a\u4eba\u8ba4\u8bc1","desc":"\u6d4b\u8bd5\u4e2a\u4eba\u8ba4\u8bc1","icon":"http:\/\/shop.com\/public\/uploads\/attach\/2019\/03\/28\/5c9ccca1c78cd.gif","id":1,"status":1,"satisfy_status":false,"cateconditions":[{"id":1,"cate_id":1,"condition_id":6,"condition_value":11,"create_time":"2019-11-26 22:22:36","update_time":"2019-11-26 22:23:17","condition":{"id":6,"name":"rztj6","desc":"\u8fd130\u5929\u53d1\u5e16\u6570\u2265","sort":0,"status":1,"create_time":"1970-01-01 08:00:00","update_time":"1970-01-01 08:00:00","satisfy":{"status":false,"value":0}}},{"id":2,"cate_id":1,"condition_id":2,"condition_value":0,"create_time":"2019-11-26 22:23:17","update_time":"2019-11-26 22:23:17","condition":{"id":2,"name":"rztj2","desc":"\u7ed1\u5b9a\u624b\u673a","sort":0,"status":1,"create_time":"1970-01-01 08:00:00","update_time":"1970-01-01 08:00:00","satisfy":{"status":true,"value":"18747755950"}}}],"cateprivileges":[{"id":1,"cate_id":1,"privilege_id":4,"create_time":"2019-11-26 21:51:28","update_time":"1970-01-01 08:00:00","built_in":0,"privilege":[{"id":4,"name":"\u4e13\u5c5e\u5ba2\u670d","desc":"\u4e13\u4eba\u5bf9\u63a5\uff0c\u4f18\u5148\u89e3\u51b3","icon":"","sort":0,"status":1,"create_time":"1970-01-01 08:00:00","update_time":"1970-01-01 08:00:00","built_in":0}]}]},"count":0}
     */
    public function cate()
    {
        $id=osx_input('id',0,'intval');
        $this->_needLogin();
        $uid=get_uid();
        $data = CateModel::getApiOne($id,$uid);
        $this->apiSuccess($data);
    }
    /**
     * @api      {get} /commonapi/Certification/icon.html 认证标识显示
     * @apiGroup certification
     * @apiName  icon
     * @apiParam {json} info
     * @apiSuccess {json} result
     */
    public function icon()
    {
        $is_certification_icon = SystemConfig::getValue('is_certification_icon');
        if (!$is_certification_icon) {
            $data['msg']='未开启显示认证图标';
            $this->apiSuccess($data);
        }
        $this->_needLogin();
        $uid=get_uid();
        $data = CateModel::getApiIcon($uid);
        $this->apiSuccess($data);
    }
    /**
     * @api      {get} /commonapi/Certification/is_certification.html 是否开启认证
     * @apiGroup certification
     * @apiName  is_certification
     * @apiParam {json} info
     * @apiSuccess {json} result
     */
    public function is_certification()
    {
        $is_certification = SystemConfig::getValue('is_certification');
        if (!$is_certification) {
            $data['msg']='未开启认证';
        }
        $data['is_certification']=$is_certification;
        $this->apiSuccess($data);
    }
    /**
     * @api      {get} /commonapi/Certification/new_msg.html 结果提示
     * @apiGroup certification
     * @apiName  new_msg
     * @apiParam {json} info
     * @apiSuccess {json} result
     */
    public function new_msg()
    {
        $is_certification = SystemConfig::getValue('is_certification');
        if (!$is_certification) {
            $data['msg']='未开启认证';
            $this->apiSuccess($data);
        }
        $this->_needLogin();
        $uid=osx_input('get.uid',0,'intval');
        if(!$uid){
            $uid=get_uid();
        }
        $page=osx_input('get.page',0,'intval');
        $page_num=osx_input('get.page_num',1,'intval');
        $is_read=osx_input('get.is_read',0,'intval');
        $list = CateModel::getApiMsgPage(['page'=>$page,'page_num'=>$page_num,'uid'=>$uid,'is_read'=>$is_read])->toArray();
        if ($list) {
            $data=$list[0];
            if ($data['status']==1) {
                $data['title']=$data['name'].'成功';
                $data['message']="恭喜您，认证成功！您提交的认证信息已通过审核。";
            }else if ($data['status']==-1){
                $data['title']=$data['name'].'未通过';
                $data['message']="很抱歉，您提交的认证信息审核未通过，具体原因：{$data['reject_note']}。

符合要求，可重新提交认证。";
            }
        }else{
            $data=[];
        }
        $this->apiSuccess($data);
    }
    /**
     * @api      {get} /commonapi/Certification/new_msg_read/id/1.html 结果提示阅读回传
     * @apiGroup certification
     * @apiName  new_msg_read
     * @apiParam {json} info
     * @apiSuccess {json} result
     */
    public function new_msg_read()
    {
        $this->_needLogin();
        $uid=get_uid();
        $id=osx_input('get.id',0,'intval');
        $params['id']=$id;
        $params['uid']=osx_input('get.uid',$uid,'intval');
        $entity=EntityModel::where($params)->find();
        if ($entity) {
            $flag = EntityModel::setRead($id);
            $this->apiSuccess($flag);
        }else{
            $flag['msg']='该认证不存在，或者您身份有误！';
            $this->apiSuccess($flag);
        }
    }
    /**
     * @api      {get} /commonapi/Certification/cate_datum_list/cate_id/1.html 类别资料项列表
     * @apiGroup certification
     * @apiName  cate_datum_list
     * @apiParam {Number} cate_id 类别id
     * @apiParam {Number} page 页码
     * @apiParam {Number} page_num 每页数量
     * @apiParam {json} info
     * @apiParamExample {json} Request-Example:
     * {"cate_id":1,"page":"1","page_num":"100"}
     * @apiSuccess {json} result
     * @apiSuccessExample {json} Success-Response:
     * {"code":200,"msg":"ok","data":[{"field":"zsxm","name":"\u771f\u5b9e\u59d3\u540d","input_tips":"","form_type":"text","setting":""},{"field":"sfzh","name":"\u8eab\u4efd\u8bc1\u53f7","input_tips":"","form_type":"text","setting":""},{"field":"scsfzzm","name":"\u4e0a\u4f20\u8eab\u4efd\u8bc1\u6b63\u9762\u56fe\u7247","input_tips":"\u6ce8\u610f\u53cd\u5149\uff0c\u4fdd\u8bc1\u8eab\u4efd\u8bc1\u5185\u5bb9\u6e05\u6670\u53ef\u89c1","form_type":"file","setting":""},{"field":"scsfzfm","name":"\u4e0a\u4f20\u8eab\u4efd\u8bc1\u53cd\u9762\u56fe\u7247","input_tips":"\u6ce8\u610f\u53cd\u5149\uff0c\u4fdd\u8bc1\u8eab\u4efd\u8bc1\u5185\u5bb9\u6e05\u6670\u53ef\u89c1","form_type":"file","setting":""},{"field":"xl","name":"\u5b66\u5386","input_tips":"","form_type":"select","setting":"\u6587\u76f2\n\u5c0f\u5b66\n\u521d\u4e2d\n\u9ad8\u4e2d(\u804c\u9ad8\u3001\u4e2d\u4e13)\n\u5927\u4e13(\u9ad8\u804c)\n\u672c\u79d1\n\u7855\u58eb\u7814\u7a76\u751f\n\u535a\u58eb\u7814\u7a76\u751f\n\u4fdd\u5bc6"}],"count":0}
     */
    public function cate_datum_list()
    {
        $cate_id=osx_input('get.cate_id',0,'intval');
        $page=osx_input('get.page',1,'intval');
        $page_num=osx_input('get.page_num',100,'intval');
        $list = CateModel::getApiCateDatumPage(['cate_id'=>$cate_id,'page'=>$page,'page_num'=>$page_num]);
        $this->apiSuccess($list);
    }
    /**
     * @api      {get} /commonapi/Certification/faq_list.html 问题列表
     * @apiGroup certification
     * @apiName  faq_list
     * @apiParam {Number} page 页码
     * @apiParam {Number} page_num 每页数量
     * @apiParam {json} info
     * @apiParamExample {json} Request-Example:
     * {"page":"1","page_num":"20"}
     */
    public function faq_list()
    {
        $page=osx_input('get.page',1,'intval');
        $page_num=osx_input('get.page_num',20,'intval');
        $list = FaqModel::getApiPage(['page'=>$page,'page_num'=>$page_num]);
        $this->apiSuccess($list);
    }
    /**
     * @api      {get} /commonapi/Certification/faq/id/1.html 问题详情
     * @apiGroup certification
     * @apiName  faq
     * @apiParam {Number} id id
     * @apiSuccess {json} result
     */
    public function faq($id)
    {
        $data = FaqModel::get($id);
        $this->apiSuccess($data);
    }
    /**
     * @api      {post} /commonapi/Certification/entity_post/cate_id/1.html 填表资料项提交(认证接口)
     * @apiGroup certification
     * @apiName  entity_post
     * @apiParam {Number} cate_id 类别id
     * @apiParam {json} info
     * @apiParamExample {json} Request-Example:
     * {"zsxm":1,"sfzh":"1"}
     */
    public function entity_post(Request $request)
    {
        $this->_needLogin();
        $uid=get_uid();
        $cate_id = osx_input('post.cate_id',0,'intval');
        $params['cate_id'] = $cate_id;
        $params['uid'] = $uid;
        $entity=EntityModel::isExist($params);
        
        $fields = CateModel::getApiCateDatumPage($params);
        $post_fields=[];
        foreach ($fields as $key => $value) {
            $post_fields[$key]=$value['field'];
        }
        $datum_data = UtilService::postMore($post_fields, $request);
        
        $post['cate_id']=$params['cate_id'];
        $post['uid']=$uid;
        $member = db('user')->where('uid',$uid)->field('uid,nickname,avatar,phone')->find();
        $post['avatar']=$member['avatar'];
        $post['nickname']=$member['nickname'];
        $post['phone']=$member['phone'];
        $post['create_time']=time();
        if (isset($datum_data['zsxm'])) {
            $post['truename']=$datum_data['zsxm'];
        }
        $post['datum_data']=serialize($datum_data);
        //扩展表
        $table_name=CateModel::find($cate_id)->table_name;
        $table_name=CateModel::getTableName($table_name,false);
        $model=db($table_name);
        if ($entity) {
            if ($entity['status']==1) {
                return JsonService::fail('当前认证已通过，请勿重复提交！');
            }
            if ($entity['status']==0) {
                return JsonService::fail('当前认证已提交，请勿重复提交！');
            }
            $post['status']=0;
            $res = EntityModel::edit($post,$entity['id']);
            $new_post=$post;
            if($model->where('entity_id',$res)->where(['uid'=>$new_post['uid']])->count()){
                $res = $model->where('entity_id',$res)->update($new_post);
            }else{
                $res = $model->insert($new_post);
            }

            //echo $model->getLastSql();exit;
        }else{
            $res = EntityModel::insertGetId($post);
            $new_post=$post;
            $new_post['entity_id']=$res;
            $res = $model->insert($new_post);
        }
        if ($res) {
            return JsonService::result('200', '认证申请提交成功', ['desc' => '我们会尽快完成审核，请耐心等待。审核结果将以系统消息告知，请注意查收。']);
        }else{
            return JsonService::fail(EntityModel::getErrorInfo());
        }
    }
}