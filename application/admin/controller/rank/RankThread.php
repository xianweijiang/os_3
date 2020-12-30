<?php

namespace app\admin\controller\rank;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Db;
use think\Cache;
use think\Request;
use think\Url;
use service\FormBuilder as Form;
use app\admin\model\rank\Rank as RankModel;
use app\admin\model\rank\RankDel;
use app\admin\model\rank\RankThread as RankThreadModel;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class RankThread extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index($type=1)
    {
        $this->assign([
            'type'=>$type,
        ]);
        return $this->fetch();
    }

    /**
     * @return json
     */
    public function rank_thread_list(){
        $where=Util::getMore([
            'type',
            ['order',''],
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(RankThreadModel::rankThreadList($where));
    }

    public function clear(){
        Cache::clear('thread_rank_list');
        Cache::rm('rank_list');
        return JsonService::successful('刷新成功');
    }

    public function rank_del($id=''){
        if($id==''){
            JsonService::fail('缺少参数');
        }
        RankThreadModel::beginTrans();
        $tid=RankThreadModel::where('id',$id)->find()->toArray();
        $data['model']='thread';
        $data['pid']=$tid['tid'];
        $res1=RankDel::insert($data);
        $res2=RankThreadModel::where('id',$id)->delete();
        if($res1!==false&&$res2!==false){
            $time1=time()-86400;
            $time2=time()-604800;
            $time3=time()-2592000;
            $tids=RankThreadModel::where('type',$tid['type'])->where('time_type',$tid['time_type'])->column('tid');
            $rank_del=RankDel::where('model','thread')->column('pid');
            switch($tid['type']){
                case 1:
                    switch($tid['time_type']){
                        case 1:
                            $thread=db('com_thread')->where('status',1)->where('type',1)->where('is_weibo',0)->where('create_time','>',$time1)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 2:
                            $thread=db('com_thread')->where('status',1)->where('type',1)->where('is_weibo',0)->where('create_time','>',$time2)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }

                            break;
                        case 3:
                            $thread=db('com_thread')->where('status',1)->where('type',1)->where('is_weibo',0)->where('create_time','>',$time3)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 4:
                            $thread=db('com_thread')->where('status',1)->where('type',1)->where('is_weibo',0)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                    }
                    break;
                case 2:
                    switch($tid['time_type']){
                        case 1:
                            $thread=db('com_thread')->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time1)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 2:
                            $thread=db('com_thread')->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time2)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 3:
                            $thread=db('com_thread')->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time3)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 4:
                            $thread=db('com_thread')->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                    }
                    break;
                case 3:
                    switch($tid['time_type']){
                        case 1:
                            $thread=db('com_thread')->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('create_time','>',$time1)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 2:
                            $thread=db('com_thread')->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('create_time','>',$time2)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 3:
                            $thread=db('com_thread')->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('create_time','>',$time3)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 4:
                            $thread=db('com_thread')->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                    }
                    break;
                case 4:
                    switch($tid['time_type']){
                        case 1:
                            $thread=db('com_thread')->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time1)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 2:
                            $thread=db('com_thread')->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time2)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 3:
                            $thread=db('com_thread')->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time3)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                        case 4:
                            $thread=db('com_thread')->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                            if($thread){
                                $map['type']=$tid['type'];
                                $map['time_type']=$tid['time_type'];
                                $map['status']=1;
                                $map['tid']=$thread['id'];
                                $map['hot']=$thread['hot'];
                                $map['sort']=100;
                                $res3=RankThreadModel::insert($map);
                                if($res3===false){
                                    RankThreadModel::rollbackTrans();
                                }
                            }
                            break;
                    }
                    break;
            }
            RankThreadModel::commitTrans();
            Cache::clear('thread_rank_list');
            return JsonService::successful('下榜成功');
        }else{
            RankThreadModel::rollbackTrans();
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
            RankThreadModel::beginTrans();
            $thread_ids=RankThreadModel::where('id','in',$post['ids'])->select()->toArray();
            $data['model']='thread';
            $data2=array();
            foreach($thread_ids as &$val){
                $data['pid']=$val['tid'];
                $data2[]=$data;
            }
            unset($val);
            $res1=RankDel::insertAll($data2);
            $res2=RankThreadModel::where('id','in',$post['ids'])->delete();
            if($res1!==false&&$res2!==false){
                $rank_del=RankDel::where('model','thread')->column('pid');
                foreach($thread_ids as &$tid){
                    $time1=time()-86400;
                    $time2=time()-604800;
                    $time3=time()-2592000;
                    $tids=RankThreadModel::where('type',$tid['type'])->where('time_type',$tid['time_type'])->column('tid');
                    switch($tid['type']){
                        case 1:
                            switch($tid['time_type']){
                                case 1:
                                    $thread=db('com_thread')->where('status',1)->where('type',1)->where('is_weibo',0)->where('create_time','>',$time1)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 2:
                                    $thread=db('com_thread')->where('status',1)->where('type',1)->where('is_weibo',0)->where('create_time','>',$time2)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }

                                    break;
                                case 3:
                                    $thread=db('com_thread')->where('status',1)->where('type',1)->where('is_weibo',0)->where('create_time','>',$time3)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 4:
                                    $thread=db('com_thread')->where('status',1)->where('type',1)->where('is_weibo',0)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                            }
                            break;
                        case 2:
                            switch($tid['time_type']){
                                case 1:
                                    $thread=db('com_thread')->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time1)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 2:
                                    $thread=db('com_thread')->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time2)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 3:
                                    $thread=db('com_thread')->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time3)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 4:
                                    $thread=db('com_thread')->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                            }
                            break;
                        case 3:
                            switch($tid['time_type']){
                                case 1:
                                    $thread=db('com_thread')->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('create_time','>',$time1)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 2:
                                    $thread=db('com_thread')->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('create_time','>',$time2)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 3:
                                    $thread=db('com_thread')->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('create_time','>',$time3)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 4:
                                    $thread=db('com_thread')->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                            }
                            break;
                        case 4:
                            switch($tid['time_type']){
                                case 1:
                                    $thread=db('com_thread')->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time1)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 2:
                                    $thread=db('com_thread')->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time2)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 3:
                                    $thread=db('com_thread')->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time3)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                                case 4:
                                    $thread=db('com_thread')->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('id','not in',$tids)->field('id,reply_count+support_count hot')->order('hot desc')->find();
                                    if($thread){
                                        $map['type']=$tid['type'];
                                        $map['time_type']=$tid['time_type'];
                                        $map['status']=1;
                                        $map['tid']=$thread['id'];
                                        $map['hot']=$thread['hot'];
                                        $map['sort']=100;
                                        $res3=RankThreadModel::insert($map);
                                        if($res3===false){
                                            RankThreadModel::rollbackTrans();
                                        }
                                    }
                                    break;
                            }
                            break;
                    }
                }
                unset($tid);
                RankThreadModel::commitTrans();
                Cache::clear('thread_rank_list');
                return JsonService::successful('下榜成功');
            }else{
                RankThreadModel::rollbackTrans();
                return JsonService::fail('下榜失败');
            }
        }
    }

}
