<?php

namespace app\admin\model\com;

use app\admin\model\com\ComForum as ForumModel;
use app\admin\model\com\ComThread as ThreadModel;
use app\admin\model\com\ComThreadClass as ThreadClassModel;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService;
use Carbon\Carbon;

/**
 * 评论 model
 * Class ComPost
 * @package app\admin\model\com
 */
class ComPost extends ModelBasic
{
    use ModelTrait;

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // protected $dateFormat         = 'Y-m-d H:i:s';

	public static function PostList($where){
		trace($where);
        $model = self::getModelObject($where)->field(['*']);
        $model->page((int)$where['page'], (int)$where['limit']);
        $data = $model->order('create_time desc')->where('is_thread',0)->select();
		if(count($data)){
			foreach ($data as &$d){
				$d['content'] = emoji_decode(strip_tags($d['content']));
				$threadInfo=ComThread::where('id',$d['tid'])->field('type,title,id,is_weibo,summary')->find()->toArray();
				$d['thread_title'] = $threadInfo['title'];
                $d['thread_id'] = $threadInfo['id'];
				switch($threadInfo['type']){
					case 1:
						$d['type']='帖子';
						break;
					case 2:
						$d['type']='动态';
						break;
					case 4:
						$d['type']='资讯';
						break;
					case 6:
						$d['type']='视频';
						break;
					case 8:
						$d['type']='聚合';
						break;
				}
				if($threadInfo['is_weibo']==1){
					$d['thread_title']=$threadInfo['summary'];
					$d['type']='动态';
				}
				$d['is_top_name']     = $d['is_top']? '是':'否';
				$d['fid_name']      =$d['fid'] == 0?'未关联版块': ForumModel::where('id',$d['fid'])->cache(1)->value('name').'【'.$d['fid'].'】';
				$d['class_id'] = $d['tid'] ? ThreadModel::getFieldById($d['tid'], 'class_id'):0;
				$d['class_name']      = $d['class_id'] == 0?'未关联分类':ThreadClassModel::where('id', $d['class_id'])->cache(1)->value('name')."【{$d['class_id']}】";
				$d['author_info'] = $d['author_uid']? db('user')->cache(1)->getFieldByUid($d['author_uid'], 'nickname') : '';
                $d['create_time']=time_format($d['create_time']);
			}
			$data = $data->toArray();
		}else{
			$data =[];
		}
		$count = self::getModelObject($where)->where('is_thread',0)->count();
		return compact('count', 'data');
	}

	public static function getModelObject($where = [])
	{
		$model = new self();
		if (!empty($where)) {
			// data 日期
			$map         = [];
			if($where['type'] != ''){
				if($where['type']==2){
					$post_id=ComThread::where('is_weibo',1)->where('status',1)->column('id');
					$map['tid']  = ['in', $post_id];
				}else{
					$fids = ComForum::where('type',$where['type'])->column('id')?:[];
					$map['fid']  = ['in', $fids];
				}
			}
			if($where['status'] != ''){
				$map['status'] = $where['status'];
			}else{
				$map['status'] = ['in', [1,2]];
			}
			if($where['fid']){
				$map['fid'] = $where['fid'];
			}
            if($where['is_vest']>-1){
                $map['is_vest'] = $where['is_vest'];
            }
			if($where['name']){
				$map['content'] = ['like', "%{$where['name']}%"];
			}
			if($where['uid']){
				$uid=db('user')->where('uid|nickname','like','%'.$where['uid'].'%')->column('uid');
				$map['author_uid'] = array('in',$uid);
			}
			if($where['tid']){
				$map['tid'] = $where['tid'];
			}else{
				if($where['type']!=2){
					$tids=ComThread::where('status',1)->column('id');
					$map['tid']=array('in',$tids);
				}
            }
			$model = self::where($map)->field(['*']);
			$model->where(function($query) use($where){
				switch ($where['data']) {
					case 'yesterday':
					case 'today':
					case 'week':
					case 'month':
					case 'year':
						$query->whereTime('create_time', $where['data']);
						break;
					case 'quarter':
						$start = strtotime(Carbon::now()->startOfQuarter());
						$end   = strtotime(Carbon::now()->endOfQuarter());
						$query->whereTime('create_time', 'between', [$start, $end]);
						break;
					case '':
						;
						break;
					default:
						$between = explode(' - ', $where['data']);
						$query->whereTime('create_time', 'between', [$between[0], $between[1]]);
						break;
				}
			});
		}
		return $model;
	}

	public static function add($data)
	{
		$object=self::create($data);
		return $object->getLastInsID();
	}

}