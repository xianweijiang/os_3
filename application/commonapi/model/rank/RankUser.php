<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\commonapi\model\rank;


use basic\ModelBasic;
use think\Db;
use think\Cache;

class RankUser extends ModelBasic
{

    public static function getList($type,$uid){
        if($type=='all'){
            $list=self::where('status',1)->order('rank asc')->limit(100)->select()->toArray();
        }else{
            $list=self::where('status',1)->order('week_rank asc')->limit(50)->select()->toArray();
        }
        $follow=db('user_follow')->where('uid',$uid)->where('status',1)->column('follow_uid');
        foreach($list as &$value){
            $value['is_follow']=in_array($value['uid'],$follow)?1:0;
            $value['avatar']=get_root_path($value['avatar']);
        }
        unset($value);
        return $list;
    }

    public static function firstUser(){
        $prefix=config('database.prefix');
        self::beginTrans();
        $user=db('user')->select();
        $map=array();
        $data['new_fans']=0;
        $data['week_rank']=0;
        $data['rank']=0;
        $data['status']=1;
        foreach($user as &$value){
            $data['uid']=$value['uid'];
            $data['nickname']=$value['nickname'];
            $data['avatar']=$value['avatar'];
            $data['fans']=$value['fans'];
            $data['last_fans']=$value['fans'];
            $data['signature']=$value['signature'];
            $map[]=$data;
        }
        unset($value);
        $res1=self::insertAll($map);
        if($res1){
            $page=1;
            $row=2000;
            do{
                $i=1;
                $uids=self::page($page,$row)->order('fans desc')->column('uid');
                $count=count($uids);
                if($count>0){
                    $update_sql='';
                    foreach ($uids as $val){
                        $rank=($page-1)*$row+$i;
                        $update_sql.="WHEN {$val} THEN $rank
                        ";
                        $i++;
                    }
                    unset($val);
                    $sql="UPDATE {$prefix}rank_user SET
new_fans=fans-last_fans,
last_fans=fans,
rank= CASE uid
  {$update_sql}
END WHERE uid in (".implode(',',$uids).')';
                    $res2=self::execute($sql);
                    if($res2===false){
                        self::rollbackTrans();
                        return false;
                    }
                }
                $page++;
            }while($count==$row);
            self::commitTrans();
            return true;
        }else{
            self::rollbackTrans();
            return false;
        }
    }

    public static function UserRank(){
        self::beginTrans();
        $res=self::_CopyDataToUserRank();//数据更新
        if($res!==false){
            self::commitTrans();
            return true;
        }else{
            self::rollbackTrans();
            return false;
        }
    }

    public static function updateUserRank($page,$page2){
        $prefix=config('database.prefix');
        self::beginTrans();
        //总排名更新start
        $row=2000;
        $i1=1;
        $uids=self::page($page,$row)->order('fans desc')->column('uid');
        $count=count($uids);
        if($count>0){
            $update_sql='';
            foreach ($uids as $val){
                $rank=($page-1)*$row+$i1;
                $update_sql.="WHEN {$val} THEN $rank
                        ";
                $i1++;
            }
            unset($val);
            $sql="UPDATE {$prefix}rank_user SET
new_fans=fans-last_fans,
last_fans=fans,
rank= CASE uid
  {$update_sql}
END WHERE uid in (".implode(',',$uids).')';
            $res2=self::execute($sql);
            if($res2===false){
                self::rollbackTrans();
                return false;
            }
            if($count==$row){
                $page++;
                $data['update_time']=time();
                $data['type']='start';
                $data['page']=$page;
                $res=db('rank_user_time')->where('id',1)->update($data);
                if($res===false){
                    self::rollbackTrans();
                    return false;
                }
            }else{
                $data['update_time']=time();
                $data['type']='end';
                $data['page']=1;
                $res=db('rank_user_time')->where('id',1)->update($data);
                if($res===false){
                    self::rollbackTrans();
                    return false;
                }
            }
        }else{
            $data['update_time']=time();
            $data['type']='end';
            $data['page']=1;
            $res=db('rank_user_time')->where('id',1)->update($data);
            if($res===false){
                self::rollbackTrans();
                return false;
            }
        }
        //总排名更新end
        //周排名更新start
        $i2=1;
        $uids2=self::page($page2,$row)->order('new_fans desc')->column('uid');
        $count2=count($uids2);
        if($count2>0){
            $update_sql='';
            foreach ($uids2 as $v){
                $weekRank=($page2-1)*$row+$i2;
                $update_sql.="WHEN {$v} THEN $weekRank
                        ";
                $i2++;
            }
            unset($v);
            $sql="UPDATE {$prefix}rank_user SET
week_rank= CASE uid
  {$update_sql}
END WHERE uid in (".implode(',',$uids2).')';
            $res2=self::execute($sql);
            if($res2===false){
                self::rollbackTrans();
                return false;
            }
            if($count2==$row){
                $page2++;
                $data['update_time']=time();
                $data['type']='start';
                $data['page2']=$page2;
                $res=db('rank_user_time')->where('id',1)->update($data);
                if($res===false){
                    self::rollbackTrans();
                    return false;
                }
            }else{
                $data['update_time']=time();
                $data['type']='end';
                $data['page2']=1;
                $res=db('rank_user_time')->where('id',1)->update($data);
                if($res===false){
                    self::rollbackTrans();
                    return false;
                }
            }
        }else{
            $data['update_time']=time();
            $data['type']='end';
            $data['page2']=1;
            $res=db('rank_user_time')->where('id',1)->update($data);
            if($res===false){
                self::rollbackTrans();
                return false;
            }
        }
        //周排名更新end
        self::commitTrans();
        return true;
    }

    private static function _CopyDataToUserRank()
    {
        $prefix=config('database.prefix');
        $sql_copy="UPDATE {$prefix}user a ,{$prefix}rank_user b SET
b.nickname=a.nickname,
b.avatar=a.avatar,
b.fans=a.fans,
b.signature=a.signature
WHERE b.uid=a.uid";
        $res=self::execute($sql_copy);//实测9W条数据只需0.3s
        return $res;
    }

}