<?php
/**
 *
 * @author: cyx<cyx@ourstu.com>
 * @day: 2019/4/12
 */

namespace app\admin\model\com;

use app\commonapi\model\TencentFile;
use service\PHPExcelService;
use service\JsonService;
use app\commonapi\controller\Sensitive;
use think\Cache;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use Carbon\Carbon;
use service\UtilService;
use app\admin\model\user\User as UserModel;
use app\admin\model\com\ComForum as ForumModel;
use app\admin\model\com\ComThreadClass as ThreadClassModel;
/**
 * 帖子主题 model
 * Class ComThread
 * @package app\admin\model\com
 */
class ComThread extends ModelBasic
{
    use ModelTrait;

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getCatTierList($model = null)
    {
        if($model === null) $model = new self();
        return UtilService::sortListTier($model->select()->toArray());
    }

    public static function getThreadType($index){
        $index_array=[
            1=>'图文帖子',
            2=>'活动帖',
            3=>'视频贴',
            4=>'资讯帖',
            5=>'活动'
        ];
        return $index_array[$index];
    }

    public static function getStatus($index){
        $index_array = [
            0  => '驳回',
            1  => '审核通过',
            2  => '待审核',
            3  => '草稿箱',
            -1 => '删除',
        ];
        return $index_array[$index];
    }



    /*
     * 获取帖子列表
     * @param $where array
     * @return array
     *
     */
    public static function ThreadList($where)
    {
        // trace($where);
        $model = self::getModelObject($where)->field(['*']);
        if ($where['excel'] == 0) $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('create_time desc')->where('is_massage',0)->select()) && count($data) ? $data->toArray() : [];
        //普通列表
        foreach ($data as &$item){
            $item['content']=json_decode($item['content']);
            $item['forum_name']      =$item['fid'] == 0?'未关联版块': ForumModel::where('id',$item['fid'])->cache(1)->value('name').'【'.$item['fid'].'】';
            $item['content'] =  mb_strcut(strip_tags(htmlspecialchars_decode(text($item['content']))),0,180,'utf-8');
            // $item['type_name']    = $item['type'].'.'.self::getThreadType($item['type']);
            $item['class_name']      = $item['class_id'] == 0?'未关联分类':ThreadClassModel::where('id', $item['class_id'])->cache(1)->value('name')."【{$item['class_id']}】";
            $item['nickname'] = $item['author_uid']? db('user')->cache(1)->getFieldByUid($item['author_uid'], 'nickname'): '';
            $author_uid              = $item['author_uid'];
            $item['is_top_name']     = $item['is_top']? '是':'否';
            $item['is_essence_name'] = $item['is_essence']?'是':'否';
            $item['admin_html']      = '';
            $item['is_recommend_name'] = $item['is_recommend']?'是':'否';
            $user_info               = UserModel::getUserInfos($author_uid);
            $item['admin_html']     .= '<a>['.$author_uid .']'. $user_info['nickname'] . "</a><br> ";
            $item['status_name']     = self::getStatus($item['status']);
            $item['create_time']=time_format($item['create_time']);
            $item['update_time']=time_format($item['update_time']);
            if($item['operation_identity']==1){
                $item['operation_nickname']=db('system_admin')->where('id',$item['operation_uid'])->value('real_name');
            }else{
                $item['operation_nickname']=UserModel::where('uid',$item['operation_uid'])->value('nickname');
            }
            switch($item['operation_identity']){
                case 1:
                    $item['operation_identity']='后台管理员';
                    break;
                case 2:
                    $item['operation_identity']='超级版主';
                    break;
                case 3:
                    $item['operation_identity']='版主';
                    break;
                case 4:
                    $item['operation_identity']='前台管理员';
                    break;
            }
            if($item['video_url']){
                $getYunConfig = TencentFile::ifYunUpload();
                $item['video_url']=TencentFile::yunKeyMediaUrl($item['video_url'],$getYunConfig['pkey']);
            }
            if($item['audio_url']){
                $getYunConfig = TencentFile::ifYunUpload();
                $item['audio_url']=TencentFile::yunKeyMediaUrl($item['audio_url'],$getYunConfig['pkey']);
            }
        }

        //是导出excel

        $count = self::getModelObject($where)->count();
        return compact('count', 'data');
    }

    /**
     * 获取连表MOdel
     * @param $model
     * @return object
     */
    public static function getModelObject($where = [])
    {
        $model = new self();
        //$model=$model->alias('p')->join('StoreProductAttrValue pav','p.id=pav.product_id','LEFT');
        if (!empty($where)) {
            // data 日期
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
            // 类型
            if($where['is_weibo'] == 0){
                if($where['type'] == ''){
                    $model->where(['type'=>['in', [1,3,5,8]]]);
                }else if($where['type'] == 6){
                    $model->where(['type'=>['in', [6,7]]]);
                }else{
                    $model->where('type', $where['type']);
                }
                $model->where('is_weibo', $where['is_weibo']);
            }else{
                $model->where('is_weibo', $where['is_weibo']);
            }
            if(isset($where['status']) && $where['status']!=''){
                $model = $model->where('status',$where['status']);
            }
            if($where['is_top'] != ''){
                $model->where('is_top', $where['is_top']);
            }
            if($where['id'] != ''){
                $model->where('id', $where['id']);
            }
            if($where['is_essence'] != ''){
                $model->where('is_essence', $where['is_essence']);
            }
            if($where['oid'] != ''){
                $model->where('find_in_set(:id,oid)',['id'=>$where['oid']]);
            }
            if (isset($where['pid']) != '') {
                $pid = $where['pid'];
//                $model = $model->whereOr('p.cate_id','LIKE',["%$catid%",$catidab]);
                $sql = " pid=$pid";
                $model->where($sql);
            }
            if(isset($where['title']) && $where['title']!=''){
                $model->where('title|summary','LIKE',"%{$where['title']}%");
                $author_uids = db('user')->where('nickname','LIKE',"%{$where['title']}%")->column('uid');
                if($author_uids){
                    $model->whereOr('author_uid', 'in', $author_uids);
                }
            }
            if(isset($where['uid']) && $where['uid']!=''){
                $author_uids = db('user')->where('nickname','LIKE',"%{$where['uid']}%")->column('uid');
                if($author_uids){
                    $model->where('author_uid', 'in', $author_uids);
                }
            }
            if(isset($where['is_recommend']) && $where['is_recommend']!=''){
                $thread_ids = ComPost::where('is_recommend',$where['is_recommend'])->where('is_thread',1)->column('tid');
                $model->where('id', 'in', $thread_ids);
            }
            if($where['fid']){
                $model->where('fid', $where['fid']);
            }
            if($where['cid']){
                $model->where('class_id', $where['cid']);
            }
            if($where['comment_num']!==''){
                if($where['comment_num']==0){
                    $model->where('reply_count',0);
                }else{
                    $model->where('reply_count', 'gt',0);
                }
            }
            if (isset($where['order']) && $where['order'] != '') {
                $model->order(self::setOrder($where['order']));
            }


        }
        return $model;
    }

    /**根据cateid查询产品 拼sql语句
     * @param $cateid
     * @return string
     */
    protected static function getCateSql($cateid)
    {
        $lcateid = $cateid . ',%';//匹配最前面的cateid
        $ccatid = '%,' . $cateid . ',%';//匹配中间的cateid
        $ratidid = '%,' . $cateid;//匹配后面的cateid
        return " `cate_id` LIKE '$lcateid' OR `cate_id` LIKE '$ccatid' OR `cate_id` LIKE '$ratidid' OR `cate_id`=$cateid";
    }

    /** 如果有子分类查询子分类获取拼接查询sql
     * @param $cateid
     * @return string
     */
    protected static function getPidSql($cateid)
    {

        $sql = self::getCateSql($cateid);
        $ids = CategoryModel::where('pid', $cateid)->column('id');
        //查询如果有子分类获取子分类查询sql语句
        if ($ids) foreach ($ids as $v) $sql .= " OR " . self::getcatesql($v);
        return $sql;
    }

    /**
     * 条件切割
     * @param string $order
     * @param string $file
     * @return string
     */
    public static function setOrder($order, $file = '-')
    {
        if (empty($order)) return '';
        return str_replace($file, ' ', $order);
    }

    /**
     * 创建帖子
     */
    public static function createThread($data)
    {
        $data['content']=html($data['content']);
        $data['content']=self::_limitPictureCount($data['content']);
        $data['content']=html($data['content']);
        $sensitive1=Sensitive::sensitive($data['title'],'后台帖子');
        if($sensitive1['status']==0){
            JsonService::fail('标题包含敏感词"'.$sensitive1['word'].'",请检查后重新输入');
        }
        $content=text($data['content']);
        $sensitive2=Sensitive::sensitive($content,'后台帖子');
        if($sensitive2['status']==0){
            JsonService::fail('内容包含敏感词"'.$sensitive2['word'].'",请检查后重新输入');
        }
        if($data['image']==''&&$data['is_auto_image']==1&&$data['from']=='HouTai'){
            $data['image']=self::_contentToImage($data['content']);
            if(!$data['image']){
                $data['image']='';
            }else{
                if(is_array($data['image'])){
                    $data['image']=json_encode($data['image']);
                }
            }
        }else{
            if($data['image']){
                $data['image']  = explode(",",$data['image']);
                $data['image']=json_encode($data['image']);
            }
        }
        $thread_data=$data;
        if(isset($thread_data['summary']) && $thread_data['type']==4){
            $sensitive3=Sensitive::sensitive($thread_data['summary'],'后台帖子');
            if($sensitive3['status']==0){
                JsonService::fail('摘要包含敏感词"'.$sensitive3['word'].'",请检查后重新输入');
            }
        }else{
            if($thread_data['is_weibo']==1){
                $thread_data['summary'] = $thread_data['content']; //获取内容的前60个字符作为摘要
            }else{
                $thread_data['summary'] = mb_substr(text(strip_tags($thread_data['content'], '<p></p><br><span></span>')),0,60,'UTF-8'); //获取内容的前60个字符作为摘要
            }
        }
        $thread_data['content'] = json_encode($thread_data['content']);
        $post_data=[
            'fid'=>$data['fid'],
            'is_thread'=>1,
            'level'=>0,
            'author_uid'=>$data['author_uid'],
            'title'=>$data['title'],
            'create_time'=>$data['create_time'],
            'status'=>$data['status'],
            'content'=>$data['content'],
            'from'=>$data['from'],
            'image'=>$data['image']
        ];

        self::beginTrans();
        try{
            $thread_id=self::add($thread_data);
            $post_data['tid']=$thread_id;
            $post_id=ComPost::add($post_data);
            self::update(['post_id'=>$post_id],['id'=>$thread_id]);
            $time=time()-86400;
            $newThread=self::where('status',1)->where('fid',$data['fid'])->where('create_time','>',$time)->limit(5)->order('create_time desc')->column('id');
            self::where('fid',$data['fid'])->update(['is_new'=>0]);
            self::where('id','in',$newThread)->update(['is_new'=>1]);
            /*UserTaskNew::newSendThread($data['author_uid']); //发帖新手任务
            UserTaskDay::daySendThread($data['author_uid']); //每日发帖任务*/
            Cache::rm('all_thread_count_follow_1');
            Cache::rm('all_thread_count_follow_2');
            Cache::rm('all_thread_count_recommend');
            Cache::rm('all_thread_count_weibo');
            Cache::rm('all_thread_count_video');
            action_log($data['author_uid'],3,'发布主题帖','com_thread',$thread_id);
            self::commitTrans();
            return $thread_id;
        }catch (\Exception $e){
            self::rollbackTrans();
            self::setErrorInfo('发布过程中出现异常！发布失败：'.self::getErrorInfo().$e->getMessage());
            return false;
        }
    }

    public static function editThread($data)
    {
        $data['content']=html($data['content']);
        $data['content']=self::_limitPictureCount($data['content']);
        $data['content']=html($data['content']);
        $sensitive1=Sensitive::sensitive($data['title'],'后台帖子');
        if($sensitive1['status']==0){
            JsonService::fail('标题包含敏感词"'.$sensitive1['word'].'",请检查后重新输入');
        }
        $sensitive2=Sensitive::sensitive($data['content'],'后台帖子');
        if($sensitive2['status']==0){
            JsonService::fail('内容包含敏感词"'.$sensitive2['word'].'",请检查后重新输入');
        }
        if($data['type']==1){
            if($data['image']==''&&$data['is_auto_image']==1&&$data['from']=='HouTai'){
                $data['image']=self::_contentToImage($data['content']);
                if(!$data['image']){
                    $data['image']='';
                }else{
                    if(is_array($data['image'])){
                        $data['image']=json_encode($data['image']);
                    }
                }
            }else{
                if($data['image']){
                    $data['image']  = explode(",",$data['image']);
                    $data['image']=json_encode($data['image']);
                }
            }
        }else{
            $data['image']=self::_contentToImage($data['content']);
            if(!$data['image']){
                $data['image']='';
            }else{
                if(is_array($data['image'])){
                    $data['image']=json_encode($data['image']);
                }
            }
        }
        unset($data['is_auto_image']);
        $thread_data=$data;
        if($thread_data['summary']==''||in_array($thread_data['type'],array(1,6))){
            if($thread_data['is_weibo']==1){
                $thread_data['summary'] = $thread_data['content']; //获取内容的前60个字符作为摘要
            }else{
                $thread_data['summary'] = mb_substr(text(strip_tags($thread_data['content'], '<p></p><br><span></span>')),0,60,'UTF-8'); //获取内容的前60个字符作为摘要
            }
        }else{
            $sensitive3=Sensitive::sensitive($thread_data['summary'],'后台帖子');
            if($sensitive3['status']==0){
                JsonService::fail('摘要包含敏感词"'.$sensitive3['word'].'",请检查后重新输入');
            }
        }
        $thread_data['content']=json_encode($thread_data['content']);
        $post_data=[
            'fid'=>$data['fid'],
            'author_uid'=>$data['author_uid'],
            'title'=>$data['title'],
            'content'=>$data['content'],
            'image'=>$data['image'],
        ];
        $result=ComPost::where('tid',$data['id'])->where('is_thread',1)->update($post_data);
        $res=self::where('id',$data['id'])->update($thread_data);
        if ($result!==false && $res!==false) {
            return true;
        }else{
            return false;
        }

    }

    public static function add($data)
    {
        $object=self::create($data);
        return $object->getLastInsID();
    }

    /**
     * 将content中的图片信息提取出来,取前三张图
     * @param $content
     * @return array|null
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _contentToImage($content)
    {
        $content = htmlspecialchars_decode($content);  //将编码过的字符转回html标签
        preg_match_all('/<img[^>]*\>/', $content, $match);  //获取图片标签
        if (count($match[0])>1) {  //若有多张图片，循环处理
            foreach ($match[0] as $k => &$v) {
                if($k==9){
                    break;
                }
                $img = substr(substr($v, 10), 0, -2);
                //从10开始才是src路径，然后再截取去掉最后的标签符号
                $length = "-" . strlen(strstr($v, 'title'));
                //组件传上来的img标签里会自动有width属性，计算这部分长度然后也去掉
                $imgs[] = substr($img, 0, $length);
                //$imgs[] = $img;
                //去掉width属性，此时只剩下一个完整路径
            }
            unset($v);
        } else {  //单图处理
            foreach ($match[0] as $k => &$v) {
                if($k==9){
                    break;
                }
                $img = substr(substr($v, 10), 0, -2);
                $length = "-" . strlen(strstr($v, 'title'));
                //组件传上来的img标签里会自动有width属性，计算这部分长度然后也去掉
                $imgs = substr($img, 0, $length);
            }
            unset($v);
        }
        if ($match[0] == null) {
            $imgs = null;
        }
        return $imgs;
    }

    public static function text($text, $addslanshes = false)
    {
        $text = nl2br($text);
        $text = strip_tags($text);
        if ($addslanshes)
            $text = addslashes($text);
        $text = trim($text);
        return $text;
    }

    /**
     * 图片限制
     * @param $content
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _limitPictureCount($content){
        //默认最多显示10张图片
        $maxImageCount = '40';

        //正则表达式配置
        $beginMark = 'BEGIN0000hfuidafoidsjfiadosj';
        $endMark = 'END0000fjidoajfdsiofjdiofjasid';
        $imageRegex = '/<img(.*?)\\>/i';
        $reverseRegex = "/{$beginMark}(.*?){$endMark}/i";

        //如果图片数量不够多，那就不用额外处理了。
        $imageCount = preg_match_all($imageRegex, $content);
        if ($imageCount <= $maxImageCount) {
            return $content;
        }

        //清除伪造图片
        $content = preg_replace($reverseRegex, "<img$1>", $content);

        //临时替换图片来保留前$maxImageCount张图片
        $content = preg_replace($imageRegex, "{$beginMark}$1{$endMark}", $content, $maxImageCount);

        //替换多余的图片
        $content = preg_replace($imageRegex, "", $content);

        //将替换的东西替换回来
        $content = preg_replace($reverseRegex, "<img$1>", $content);

        //返回结果
        return $content;
    }

    /**
     * 添加马甲评论
     * @param $ids
     * @param $time
     * @param $num
     * @param $tem
     * @param $content
     */
    public static function add_vest_comment($ids,$time,$num,$tem,$content){
        $table=db('com_thread');
        $data=[];
        $user=Vest::get_vest_user();
        $temp=CommentTemplate::get_vest_template();
        $difference=$time[1]-$time[0];
        foreach ($ids as $vo){
            $t_num=$num;
            $forum=$table->where(['id'=>$vo])->field('fid,post_id,create_time')->find();
            $time[0]=$forum['create_time']>$time[0]?$forum['create_time']:$time[0];
            //缓存版块缓存
            $tag='forum_other_info_fid_'.$forum['fid'];
            Cache::set($tag,null);
            Cache::rm('com_index_top');
            Cache::clear('thread_list_cache');
            do{
                $value['fid']=$forum['fid'];
                $value['tid']=$vo;
                $value['create_time']=$time[0]+mt_rand(0,$difference);
                $value['status']=1;
                $value['is_vest']=1;
                $value['level']=1;
                $value['author_uid']=$user[array_rand($user)];
                if($tem==1){
                    $value['content']=$temp[array_rand($temp)];
                }else{
                    $value['content']=$content;
                }
                $value['form']='HouTai';
                $t_num--;
                $data[]=$value;
                unset($value);
            }while($t_num>0);
            db('com_post')->where(['id'=>$forum['post_id']])->setInc('comment_count',$num);
            $table->where(['id'=>$vo])->setInc('view_count',$num*2);
            $table->where(['id'=>$vo])->setInc('reply_count',$num);
        }
        return ComPost::insertAll($data);
    }
}