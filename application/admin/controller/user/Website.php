<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */
namespace app\admin\controller\user;
use app\admin\controller\AuthController;
use app\commonapi\model\WebsiteConnect;
use service\JsonService;
use service\UtilService;
use traits\CurdControllerTrait;
/**
 * 用户管理控制器
 * Class User
 * @package app\admin\controller\user
 */
class Website extends AuthController
{
    use CurdControllerTrait;
    /**
     * 事件通知记录
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function index()
    {
        $where=UtilService::getMore([
            ['status',3],
        ]);
        $this->assign('status',$where['status']);
        return $this->fetch();
    }

    /**
     * 异步查找通知列表
     *
     * @return json
     */
    public function notify_list(){
        $where=UtilService::getMore([
            ['page',1],
            ['limit',20],
            ['status',3],
        ]);
        return JsonService::successlayui(WebsiteConnect::getNotifyList($where));
    }
}
