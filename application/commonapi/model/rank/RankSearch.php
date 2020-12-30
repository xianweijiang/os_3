<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\commonapi\model\rank;


use basic\ModelBasic;
use think\Db;
use app\commonapi\model\rank\Rank;


class RankSearch extends ModelBasic
{

    /**
     * qhy
     * 热搜排行榜
     */
    public static function getList(){
        $list=self::where('status',1)->where('is_del',0)->where('end_time',0)->whereOr('end_time','>',time())->order('sort desc,num desc')->limit(50)->select();
        return $list;
    }

    public static function addRankSearch($keyword){
        $id=self::where('keyword',$keyword)->value('id');
        if($id){
            self::where('id',$id)->setInc('num');
        }else{
            $data['keyword']=$keyword;
            $data['type']=1;
            $data['sort']=0;
            $data['num']=1;
            $data['status']=1;
            $data['end_time']=0;
            $data['	is_del']=0;
            self::insert($data);
        }
        return true;
    }
    

}