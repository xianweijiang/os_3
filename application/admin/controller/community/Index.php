<?php

/**
 * Created by PhpStorm.
 * User: zxh
 * Date: 2020/1/15
 * Time: 9:20
 */
namespace app\admin\controller\community;


use app\admin\controller\AuthController;
use app\osapi\model\com\CommunityCount;
use service\JsonService as Json;
use service\UtilService as Util;
use think\Request;

class Index extends AuthController
{

    public function get_census_message(){
        return Json::successful(CommunityCount::getCensusList());
    }

    public function get_census(Request $request){
        $data = Util::postMore([
            ['limit',''],
            ['field','forum']
        ],$request);
        return Json::successful(CommunityCount::getCensusLimit($data['limit'],$data['field']));
    }

    public function census_rank(Request $request){
        $data = Util::postMore([
            'order'
        ],$request);
        Json::successful(CommunityCount::censusList($data['order']));
    }
}