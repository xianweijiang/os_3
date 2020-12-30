<?php
/**
 * Created by PhpStorm.
 * User: zxh
 * Date: 2019/9/29
 * Time: 10:07
 */
namespace app\admin\controller\prohibit;

use app\admin\controller\AuthController;
use app\admin\model\prohibit\ProhibitReason;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Request;
use think\Url;
use app\admin\model\system\SystemAdmin;
use app\admin\model\prohibit\Prohibit as ProhibitModel;

class Prohibit extends AuthController
{

    public function index($type=1){
        $reason=ProhibitReason::where('status',1)->select();
        $time=db('report_prohibit')->where('status',1)->select();
        foreach($time as &$value){
            switch($value['time_type']){
                case 1:
                    $value['time_type']='小时';
                    break;
                case 2:
                    $value['time_type']='天';
                    break;
            }
        }
        unset($value);
        $this->assign([
            'reason'=>$reason,
            'type'=>$type,
            'time'=>$time,
            'year'=>getMonth('y'),
        ]);
        return $this->fetch();
    }

    public function prohibit_list(){
        $where=Util::getMore([
            ['type',1],
            ['identity', ''],
            ['time', ''],
            ['status', ''],
            ['data', ''],
            ['reason', ''],
            ['page',1],
            ['limit',20],
        ]);
        if($where['type']==1){
            return JsonService::successlayui(ProhibitModel::ProhibitList($where));
        }else{
            return JsonService::successlayui(ProhibitModel::ProhibitAllList($where));
        }
    }

    /**
     * 解除禁言
     * @param $id
     * @author qhy
     */
    public function relieve($id){
        $data['relieve_uid']=SystemAdmin::activeAdminIdOrFail();
        $data['relieve_identity']=1;
        $data['status']=2;
        $res = ProhibitModel::where(['id'=>$id])->update($data);
        if($res) {
            return Json::successful('解除成功!');
        }else{
            return Json::fail('解除失败!');
        }
    }

    /**
     * 批量解除禁言
     * @author qhy
     */
    public function verify()
    {
        $post = Util::postMore([
            ['ids', []]
        ]);
        if (empty($post['ids'])) {
            return JsonService::fail('请选择需要解除禁言的用户');
        } else {
            $data['relieve_uid']=SystemAdmin::activeAdminIdOrFail();
            $data['relieve_identity']=1;
            $data['status']=2;
            $res = ProhibitModel::where('id', 'in', $post['ids'])->update($data);
            if($res){
                return JsonService::successful('解除成功');
            }else{
                return JsonService::fail('解除失败');
            }

        }
    }

}