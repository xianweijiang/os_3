<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:23
 */
namespace app\admin\model\com;
use app\admin\model\system\SystemConfig;
use app\osapi\model\user\UserModel;
use Doctrine\Common\Cache\Cache;
use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

class Vest extends ModelBasic
{
    use ModelTrait;

    public static function addDate($data){
        self::beginTrans();
        $data['account']=$data['phone'];
        $data['is_vest']=1;
        $data['follow']=$data['follow_count'];
        $data['bind_uid']=db('user')->insertGetId($data);
        unset($data['account'], $data['is_vest'], $data['follow']);
        if(!$data['bind_uid']){
            self::rollbackTrans();
            return false;
        }
        //添加关注
        $follow_res=self::addFollows($data['bind_uid'],$data['follow_count']);
        if(!$follow_res){
            self::rollbackTrans();
            return false;
        }
        $data['status']=1;
        $data['create_time']=time();
        $res=self::set($data);
        if($res){
            self::commitTrans();
            return true;
        }else{
            self::rollbackTrans();
            return false;
        }
    }

    public static function addFollows($uid,$count){
        $uids=db('user')->where(['status'=>1,'uid'=>['gt',1]])->field('uid')->limit(1000)->cache('follow_user_1000')->select();
        $add_data=[];
        $data['uid']=$uid;
        $data['create_time']=time();
        $data['status']=1;
        do{
            $data['follow_uid']=$uids[array_rand($uids)]['uid'];
            unset($uids[array_rand($uids)]);
            $add_data[]=$data;
            $count--;
        }while($count>0);
       return db('user_follow')->insertAll($add_data);
    }

    public static function editData($data){
        if($data['id']){
            //判断是否是添加手机
            if($data['bind_uid']>0){
                $data['follow']=self::where(['id'=>$data['id']])->value(['follow_count']);
                db('user')->where(['uid'=>$data['bind_uid']])->update($data);
            }
            return self::where(['id'=>$data['id']])->update($data);
        }else{
            return self::addDate($data);
        }
    }
    public static function getDate($id){
        $map['id']=$id;
        return self::where($map)->find();
    }
    public static function setStatus($map,$status){
        //将用户变成匿名
        $uid=db('vest')->where($map)->field('bind_uid')->select();
        $uids=array_column($uid,'bind_uid');
        $data['nickname']='匿名';
        $data['status']=2;
        db('user')->where(['uid'=>['in',$uids]])->update($data);
        return self::where($map)->update(['status'=>$status]);
    }

    public static function get_vest_list($map,$page,$limit,$order){
        $data=self::where($map)->page($page,$limit)->order($order)->select();
        foreach ($data as &$v){
            $v['create_time']=date('Y-m-d H:i:s',$v['create_time']);
            switch ( $v['sex']){
                case 1:$sex='男';break;
                case 2:$sex='女';break;
                default:$sex='保密';
            }
            $v['sex']=$sex;
        }
        $count=self::where($map)->count();
        return compact('count', 'data');
    }

    public static function add_vest($number,$min,$max){
//        $data=[];
        $avatar_nickname=self::get_avatar_nickname();
        $time=[];
        do{
            list($value['avatar'], $value['nickname'])=self::get_nickname($avatar_nickname);
            $value['sex']=0;
            $value['attribute']='马甲';
            $value['create_time']=time();
            $value['status']=1;
            $value['follow_count']=rand($min,$max);
            $value['phone']='';
//            $data[]=$value;
            $number--;
            self::addDate($value);
        }while($number>0);
        cache('time_value',$time);
        return true;
    }

    public static function get_vest_user(){
       $user= self::where(['status'=>1,'bind_uid'=>['gt',0]])->field('bind_uid')->select()->toArray();
       return array_column($user,'bind_uid');
    }

    public static function get_avatar_nickname(){
        $data = cache('new_url_avatar_list');
        ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; GreenBrowser)');
        if(!$data){
            $data=[];
            $num=3;
            $url='https://www.douban.com/explore/';
            $name_list = '#target="_blank">(.*)(?)</a#';
            $img_list='#src="(.*)(?)"#';
            do{
                sleep(0);
                $content = file_get_contents($url);
                $start = strpos($content, 'grid-16-8 clearfix');
                $end = strpos($content, 'gallery-latest');
                $content = mb_substr($content, $start, $end - $start, 'utf-8');
                unset($start,$end);
                for ($j = 0; $j < 10; $j++) {
                    $start = strpos($content, 'usr-pic');
                    $end = strpos($content, 'column');
                    $str = mb_substr($content, $start, $end - $start, 'utf-8');
                    $content = mb_substr($content, $end, null, 'utf-8');
                    preg_match_all($name_list, $str, $matches_name_list);
                    if (count($matches_name_list[1]) == 2) {
                            preg_match_all($img_list, $matches_name_list[1][0], $matches_img_list);
                            if($matches_img_list[1]){
                                $value['avatar'] = $matches_img_list[1][0];
                                $value['nickname'] = $matches_name_list[1][1];
                                $data[] = $value;
                            }
                    }
                    unset($start,$end,$str,$matches_img_list,$matches_name_list);
                }
                $num--;
            }while($num>0);
            cache('new_url_avatar_list',$data);
        }
        unset($content);
        return $data;
    }

    /**
     * 获取马甲用户名
     * @param $avatar_nickname
     * @return array
     */
    public static function get_nickname($avatar_nickname){
        if(count($avatar_nickname)==0){
            cache('new_url_avatar_list',null);
            $avatar_nickname=self::get_avatar_nickname();
        }
        $id=array_rand($avatar_nickname);
        $avatar=$avatar_nickname[$id]['avatar'];
        $nickname=$avatar_nickname[$id]['nickname'];
        $nicknameList=cache('nickname_all_user');
        if(!$nicknameList){
            $nicknameList=db('user')->where(['status'=>['gt',-1]])->group('nickname')->field('nickname')->select();
            $nicknameList=array_column($nicknameList,'nickname');
            cache('nickname_all_user',$nicknameList,3600);
        }
        if(in_array($nickname,$nicknameList)){
            unset($avatar_nickname[$id]);
            cache('new_url_avatar_list',$avatar_nickname);
            return self::get_nickname($avatar_nickname);
        }else{
            $nicknameList[]=$nickname;
            unset($avatar_nickname[$id]);
            cache('new_url_avatar_list',$avatar_nickname);
            cache('nickname_all_user',$nicknameList,3600);
            return [$avatar,$nickname];
        }
    }
}