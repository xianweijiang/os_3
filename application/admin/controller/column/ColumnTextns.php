<?php

namespace app\admin\controller\column;

use app\admin\model\order\StoreOrder;
use app\admin\model\store\StoreProduct;
use service\FileService;
use think\Db;
use think\Url;
use think\Request;
use service\JsonService;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use service\FormBuilder as Form;
use service\UploadService as Upload;
use app\admin\model\column\ColumnText;
use app\admin\model\system\SystemConfig;
use app\admin\controller\AuthController;

use service\PHPTreeService as Phptree;
use app\admin\model\article\ArticleCategory as ArticleCategoryModel;
use app\admin\model\article\Article as ArticleModel;
use app\admin\model\system\SystemAttachment;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use service\FileService as FileClass;

//引入腾讯云点播上传视频和音频
require 'vod-sdk-v5/autoload.php';
use Vod\VodUploadClient;
use Vod\Model\VodUploadRequest;

class ColumnTextns extends AuthController
{
    /**
    * 文本编辑
    */
    public function createtext()
    {
        $where = Util::getMore([
            ['title',''],
            ['cid','']
        ],$this->request);
        $pid=osx_input('get.id',0,'intval');
        $this->assign('where',$where);
        $where['merchant'] = 0;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        //获取分类列表
        $tree = [];
        $this->assign(compact('tree','pid'));
        return $this->fetch();
    }

    /**
    * 音频编辑
    */
    public function createmusics()
    {
        $where = Util::getMore([
            ['title',''],
            ['cid','']
        ],$this->request);
        $pid=osx_input('get.id',0,'intval');
        $this->assign('where',$where);
        $where['merchant'] = 0;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        //获取分类列表
        $tree = [];

				//获得腾讯云点播后台系统配置信息
				$getIfYun = self::ifYunUpload();
				$switch = $getIfYun['switch'];
				$this->assign('switch',$switch);

        $this->assign(compact('tree','pid'));
        return $this->fetch();
    }

    /**
    * 视频编辑
    */
    public function createvideos()
    {
        $where = Util::getMore([
            ['title',''],
            ['cid','']
        ],$this->request);
        $pid=osx_input('get.id',0,'intval');
        $this->assign('where',$where);
        $where['merchant'] = 0;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        //获取分类列表
        $tree = [];

        //获得腾讯云点播后台系统配置信息
				$getIfYun = self::ifYunUpload();
				$switch = $getIfYun['switch'];
				$this->assign('switch',$switch);

				$this->assign(compact('tree','pid'));
        return $this->fetch();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function create(Request $request)
    {
        $data = Util::postMore([
            ['name',''],
            ['img',''],
            ['content',''],
            'type',
            'pid',
            ['is_show',0],
            ['is_read',0],
            ['sort',0],
        ],$request);
        $data['content']=osx_input('post.content','','html');
        $data['info']=input('post.info','','html');
        $data['create_time'] = time();

				//后台是否配置云点播:0否1是
				$getIfYun = self::ifYunUpload();
				$switch = $getIfYun['switch'];
				if($data['type']==1){
					$data['m_type'] = 0;
				}else{
					if( $switch==1 ){
						$data['m_type'] = 1;
					}else{
						$data['m_type'] = 0;
					}
					$data['media_url'] = $data['info'];
				}
		$res = ColumnText::set($data);
        $product=StoreProduct::where('id',$data['pid'])->find();
        $uids=db('store_cart')->where('product_id',$product['id'])->where('is_pay',1)->column('uid');
        if($data['is_show']==1){
            $set=MessageTemplate::getMessageSet(38);
            $length_title=mb_strlen($product['store_name'],'UTF-8');
            if($length_title>7){
                $product['store_name']=mb_substr($product['store_name'],0,7,'UTF-8').'…';
            }
            $template=str_replace('{专栏名称}', $product['store_name'], $set['template']);
            if($set['status']==1){
                $message=array();
                $data1['from_uid']=0;
                $data1['content']=$template;
                $data1['type_id']=1;
                $data1['title']=$set['title'];
                $data1['from_type']=1;
                $data1['route']='column_details';
                $data1['link_id']=$product['id'];
                $data1['create_time']=time();
                $data1['send_time']=time();
                $map1=$data1;
                foreach($uids as &$value){
                    $data1['to_uid']=$value;
                    $message[]=$data1;
                }
                unset($value);
                Message::insertAll($message);
                $message_list=Message::where($map1)->select()->toArray();
                $data2['is_read']=0;
                if($set['popup']==1){
                    $data2['is_popup']=0;
                }else{
                    $data2['is_popup']=1;
                    $data2['popup_time']=time();
                }
                $data2['is_sms']=0;
                $data2['type']=1;
                $data2['create_time']=time();
                $message_read=array();
                foreach($message_list as &$item){
                    $data2['uid']=$item['to_uid'];
                    $data2['message_id']=$item['id'];
                    $message_read[]=$data2;
                }
                MessageRead::insertAll($message_read);
            }
        }

        return Json::successful('添加产品成功!');
    }

    /**
     * 修改界面，及默认数据
     * [ups description]
     * @param  [type] $id   [description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function ups()
    {
        $id=osx_input('id',0,'intval');
        $type=osx_input('type','');
        $data = ColumnText::contents($id);

				//获得腾讯云点播后台系统配置信息
				$getIfYun = self::ifYunUpload();
				$switch = $getIfYun['switch'];
				$this->assign('switch',$switch);

        $this->assign(compact('data'));
        switch ($type) {
            case 1:
                $temp = 'uptext';
                break;
            case 2:
                $temp = 'upmusic';
                break;
            case 3:
                $temp = 'upvideos';
                break;
        }
        return $this->fetch($temp);
    }
    public function des()
    {
        $id=osx_input('id',0,'intval');
        $res = ColumnText::get(['id' => $id])->delete();
        if (!$res) return Json::successful('删除失败!');
        return Json::successful('删除成功!');
    }
    /**
     * 修改数据库
     * [updates description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function updates(Request $request)
    {
        $data = Util::postMore([
            ['name',''],
            ['img',''],
            'type',
            'id',
            ['is_show',0],
            ['is_read',0],
            ['sort',0],
        ],$request);
        $data['info']=osx_input('post.info','','html');
        $data['content']=osx_input('post.content','','html');
				//后台是否配置云点播:0否1是
				$getIfYun = self::ifYunUpload();
				$switch = $getIfYun['switch'];
				if($data['type']==1){
					$data['m_type'] = 0;
				}else{
					if( $switch==1 ){
						$data['m_type'] = 1;
					}else{
						$data['m_type'] = 0;
					}
					$data['media_url'] = $data['info'];
				}
        $id = $data['id'];
        unset($data['id']);

        $url = ColumnText::where('id',$id)->find()['info'];

        if ($data['info'] != $url) {
            @unlink($_SERVER['DOCUMENT_ROOT'].$url);
        }
        $res = ColumnText::edit($data,$id);
        if ($res) {

					return Json::successful('修改成功');
        }
        return Json::successful('修改失败');
    }

	/**
	 * 获取腾讯视频云点播系统配置信息
	 * @param
	 * @return array
	 */
	public function ifYunUpload(){
		$getTencentConfig = SystemConfig::getMore(['tencent_video_is_open','tencent_video_secret_id','tencent_video_secret_key','tencent_video_procedure','tencent_video_save_key']);

		$string['switch'] = $getTencentConfig['tencent_video_is_open'];
		$string['sid'] = $getTencentConfig['tencent_video_secret_id'];
		$string['skey'] = $getTencentConfig['tencent_video_secret_key'];
		$string['pkey'] = $getTencentConfig['tencent_video_save_key'];

		return $string;
	}

	/* 视频上传处理并调用云点播
   * 说明: 1.先上传到服务器获取视频本地路径
	 *       2.调用云点播上传
	 *       3.删除服务器视频
	 *       4.本地上传模式沿用并保留视频文件
   * */
	public function mediaUpload()
	{
		//先上传本地服务器,在Video目录之下建立与本地上传模式不同的temp文件夹
		$postSaveTypePath = input('post.path');
		$resPath = Upload::file('file',$postSaveTypePath.'-tencentTemp');

		if(strpos(PUBILC_PATH,'public') == false){
			$resPath -> dir = str_replace('public/','',$resPath->dir);
		}

		$onlyPath = $resPath->uploadPath;
		$onlyFileName = basename($resPath->dir);

		/*$serverPath = $_SERVER['DOCUMENT_ROOT'];//注:本地使用*/
		$uploadFilePath = $resPath -> dir;

		/*$newPath = $serverPath.$uploadFilePath;//注:本地使用*/
		$newPath = getcwd().$uploadFilePath;
		//$reNewPath =  str_replace('\\','/',$newPath);
		/*$nowFilePath =  str_replace('/','\\',$newPath);//注:本地使用,判断与上传均使用$nowFilePath,服务器使用$newPath*/
		if(file_exists($newPath)){
			//调用腾讯云点播从本地服务器上传
			$getYunUpload = self::yunUpload($newPath); //服务器使用

			$mediaFieldID = $getYunUpload['fileID'];
			$mediaPlayURL = $getYunUpload['mediaURL'];

			if($mediaFieldID){
				//执行删除临时文件操作
				$del = $this -> deleteMediaFile($onlyPath,$onlyFileName);
				if($del==1){
					$res = array(
						'status' => 1,
						'msg'  => '上传成功！',
						'src' => $mediaPlayURL
					);
				}else{
					$res = array(
						'status' => 1,
						'msg'  => '上传成功！请手动删除服务器临时文件！',
						'src' => $mediaPlayURL
					);
				}
				return json_encode($res);
			}else{
				$mediaError = $getYunUpload['e'];
				$res = array(
					'status' => -1,
					'msg'  => $mediaError
				);
				return json_encode($res);
			}
		}else{
			$res = array(
				'status' => -1,
				'msg'  => '上传失败！文件不存在！'
			);
			return json_encode($res);
		}
	}

	/** 云点播上传
	 * @return bool|mixed|string
	 * 说明：1.上传方法会根据上传文件长度自动分片
	 *      2. 自动断点续传
	 */
	public function yunUpload()
	{
        $filePath=osx_input('filePath','','text');
		//获得腾讯云点播配置信息
		$getYunConfig = self::ifYunUpload();

		$client = new VodUploadClient($getYunConfig['sid'], $getYunConfig['skey']);
		$request = new VodUploadRequest();

		$request -> MediaFilePath = $filePath;

		try{
			$rsp = $client -> upload("ap-guangzhou", $request);
			//云媒体文件标识
			$fileID = $rsp->FileId;
			//云媒体文件播放地址
			$mediaURL = $rsp->MediaUrl;
			if($fileID){
				$result = array(
					'fileID' => $fileID,
					'mediaURL'  => $mediaURL
				);
			}
			return $result;
		}catch(Exception $e){
			$result = array(
				'e'  =>'上传失败.['.$e.']'
			);
			return $result;
		}
	}

	/** 删除本地服务器上传文件
	 * @param $filePath 文件相对路径
	 * @param $fileName 当前上传文件名(被占用无法删除)
	 * @return bool|mixed|string
	 * 说明: 1.当前上传文件名被占用无法删除
	 *       2.删除路径中除当前文件的所有其它文件
	 *       3.云点播上传成功时执行删除操作，避免临时文件目录冗余
	 */
	private function deleteMediaFile($filePath,$fileName)
	{
		//重组并调整为完整目录格式
		$getWholePath = $_SERVER['DOCUMENT_ROOT'].'/'.$filePath;
		$correctPath = str_replace('\\','/',$getWholePath);

		if(is_dir($correctPath)){
			$handle = opendir($correctPath);

			while( false!== ( $item = readdir($handle) )) {
				if ($item != '.' && $item != '..' && $item !=$fileName){
					$items = $correctPath.'/'.$item;
					unlink( $items );
				}
			}
			closedir($handle);
			return 1;
		}else{
			return 0;
		}
	}


}
