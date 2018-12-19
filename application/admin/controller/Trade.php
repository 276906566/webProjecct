<?php
namespace app\admin\controller;

use think\Cache;
use think\Session;
use think\Request;
use think\Controller;
use think\Collection;
use think\cache\driver\Redis;
use app\admin\model\ProductCate as ProductCateModel;

class Trade extends Controller
{
    protected $params;
    
    public function __construct()
    {
        parent::__construct();
        global $cacheRedis;                                           //创建Redis实例
        $cacheRedis = new Redis();
    }
    
    public function index()
    {
        return $this->fetch('product_list');
    }

    public function reload(Request $request)
    {
        global $cacheRedis;                                           //引用全局变量
        $data           = $request->param('index');
        $curuserid      = Session::get('user_id');                    //当前账号的id值
        $startStationId = array();                                    //当前用户关注的站点
        $lastDatasId    = array();                                    //当前用户关注站点的所有数据
        $redis          = $cacheRedis->handler();
        //获取当前账号能够查看的站点编号
        $startStationId = array('1','a','f','16','17');
        $lastpull = $redis->get('lastpull:userid:'.$curuserid);        //获取上次拉取的数据id
        if(!$lastpull)
        {
            $lastpull = 0;
        }
        foreach ($startStationId as $value)                           //将所有关注的站点的数据全部提取出来放到$lastDatasId
        {
            $temp_string = '0000'.$value;
            $trim_length = strlen($temp_string)-4;
            $newNumber   = substr($temp_string,$trim_length,4);
            $curUserData ='users:id:'.$newNumber;    
            $curDatas    = $redis->lrange($curUserData,0,10);//从每个站点上传的链表中取数据(每个链表数据个数为100)
            $lastDatasId = array_merge($lastDatasId,$curDatas);
        }
        sort($lastDatasId,SORT_NUMERIC);                              //对数组$lastDatas中的元素按数字方式进行升序排序
//        dump($lastDatasId);
        if(!empty($lastDatasId))
        {
            $redis->set('lastpull:userid:'.$curuserid,end($lastDatasId)+1);              //更新每个用户存取位置标记
            $CuruserShowReceive = [];
            foreach ($lastDatasId as $val)                                                  //获取关注站点的详细数据
            {
                $show_hash_datas = $redis->hmget('Receive:reveiveId:'.$val,array('id','sourceIp','sourceId','targetId','timeime','content'));
                $CuruserShowReceive[] = array(
                        'sourceIp'   => $show_hash_datas['sourceIp'], 
                        'sourceId'   => $show_hash_datas['sourceId'], 
                        'targetId'   => $show_hash_datas['targetId'],
                        'time'       => $show_hash_datas['timeime'],
                        'content'    => $show_hash_datas['content'],
                        'ukey'       => $show_hash_datas['id'],
                );
            }
        }
        else
        {
            $CuruserShowReceive = null;
        }
//        dump($CuruserShowReceive);
        $this->params['code']  =200;
        $this->params['msg']   ='查询成功';
        $this->params['data']  =$CuruserShowReceive;
        return json($this->params);
    }

    public function cateFind(Request $request)
    {
        if($request->isPost())
        {
            $data =ProductCateModel::withTrashed()->field('mid,pid,name,file,open')->select();
            if(is_null($data))
            {
                $this->params['code']  =400;
                $this->params['msg']   ='查询失败';
                $this->params['data']  ='';
            }
            else
            {
                $this->params['code']  =200;
                $this->params['msg']   ='查询成功';
                $this->params['data']  =$data;
            }
        }
        return json($this->params);
    }
}