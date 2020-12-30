<?php

namespace app\admin\controller\rank;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Db;
use think\Request;
use think\Url;
use think\Cache;
use app\admin\model\rank\RankDel;
use service\FormBuilder as Form;
use app\admin\model\rank\Rank as RankModel;
use app\admin\model\rank\RankTopic as RankTopicModel;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class RankTopic extends AuthController
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

    /**
     * @return json
     */
    public function rank_topic_list(){
        $where=Util::getMore([
            ['order',''],
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(RankTopicModel::rankTopicList($where));
    }

    public function clear(){
        Cache::rm('topic_rank_list');
        Cache::rm('rank_list');
        return JsonService::successful('刷新成功');
    }

    public function rank_del($id=''){
        if($id==''){
            JsonService::fail('缺少参数');
        }
        RankTopicModel::beginTrans();
        $oid=RankTopicModel::where('id',$id)->find()->toArray();
        $data['model']='topic';
        $data['pid']=$oid['oid'];
        $res1=RankDel::insert($data);
        $res2=RankTopicModel::where('id',$id)->delete();
        if($res1!==false&&$res2!==false){
            $oids=RankTopicModel::where('status',1)->column('oid');
            $rank_del=RankDel::where('model','thread')->column('pid');
            $topic=db('com_topic')->where('status',1)->where('id','not in',$rank_del)->where('id','not in',$oids)->field('id,view_count+post_count hot')->order('hot desc')->find();
            if($topic){
                $map['status']=1;
                $map['oid']=$topic['id'];
                $map['hot']=$topic['hot'];
                $map['sort']=50;
                $res3=RankTopicModel::insert($map);
                if($res3===false){
                    RankTopicModel::rollbackTrans();
                }
            }
            RankTopicModel::commitTrans();
            Cache::rm('topic_rank_list');
            return JsonService::successful('下榜成功');
        }else{
            RankTopicModel::rollbackTrans();
            return JsonService::fail('下榜失败');
        }
    }

    public function del_all(){
        $post = Util::postMore([
            ['ids', []]
        ]);
        if (empty($post['ids'])) {
            return JsonService::fail('请选择需要下榜的数据');
        }else{
            RankTopicModel::beginTrans();
            $topic_ids=RankTopicModel::where('id','in',$post['ids'])->select()->toArray();
            $data['model']='topic';
            $data2=array();
            foreach($topic_ids as &$val){
                $data['pid']=$val['oid'];
                $data2[]=$data;
            }
            unset($val);
            $res1=RankDel::insertAll($data2);
            $res2=RankTopicModel::where('id','in',$post['ids'])->delete();
            if($res1!==false&&$res2!==false){
                $count=count($post['ids']);
                $oids=RankTopicModel::where('status',1)->column('oid');
                $rank_del=RankDel::where('model','topic')->column('pid');
                $topic=db('com_topic')->where('status',1)->where('id','not in',$rank_del)->where('id','not in',$oids)->field('id,view_count+post_count hot')->order('hot desc')->limit($count)->select();
                $map1=array();
                $data3['status']=1;
                foreach($topic as &$value){
                    $data3['oid']=$value['id'];
                    $data3['hot']=$value['hot'];
                    $data3['sort']=50;
                    $map1[]=$data3;
                }
                unset($value);
                if($map1){
                    $res1=RankTopicModel::insertAll($map1);
                    if($res1===false){
                        RankTopicModel::rollbackTrans();
                        return false;
                    }
                }
                RankTopicModel::commitTrans();
                Cache::rm('topic_rank_list');
                return JsonService::successful('下榜成功');
            }else{
                RankTopicModel::rollbackTrans();
                return JsonService::fail('下榜失败');
            }
        }
    }

}
