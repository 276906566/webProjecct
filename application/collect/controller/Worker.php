<?php
namespace app\collect\controller;

use think\worker\Server;
use Workerman\Lib\Timer; 
use think\cache\driver\Redis;
use app\collect\model\CollectData as CollectDataModel;
//require_once __DIR__ . '/Workerman/Autoloader.php';


class Worker extends Server
{
/***做TCP服务端，必须要使用心跳来检测客户端是否因极端情况（断电、异常关机）
    而导致断开（这种状况服务端是无法立即得知客户端的断开状态的）*********/
    protected $socket = 'tcp://0.0.0.0:2346';

     /**
     * 收到信息(cannet的每个心跳包的间隔是2s)
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        global $cacheRedis; 
        $arr1=str_split($data);                          //把字符串$data分割到数组中
        if(($arr1[12]==chr(0x55))&&($arr1[0]==chr(0xAA)))//心跳包
        {
            //给connection临时设置一个lastMessageTime属性，用来记录上次收到消息的时间
            $connection->lastMessageTime = time();
        }    
        else                                             //客户端的数据
        {
            if($arr1[0]==chr(0x88))                      //chr()从指定的ASCII值返回字符。
            {
                /***********************向数据库中存储数据***************************/
                $fromid=$connection->id;
                $arr4=bin2hex($data);  

                $tempData = [
                    'sourceIp'   =>  $connection->getRemoteIp(),
                    'sourceId'   =>  bin2hex(substr($data,2,2)),
                    'targetId'   =>  bin2hex(substr($data,4,1)),
                    'time'       =>  time(),
                    'content'    =>  $arr4
                ];
                $this->createRedisList($cacheRedis,$tempData);   //将客户端上传的数据发到内存缓冲区
                /**********************向can卡发送数据******************************/
                //用于中心下方can数据给网络
                $arr2=array(chr(0x88),chr(0x12),chr(0x33),chr(0x44),chr(0x55),chr(0x01),chr(0x02),chr(0x03),chr(0x04),chr(0x05),chr(0x06),chr(0x07),chr(0x88));
                $arr3=implode("",$arr2);
                $connection->send($arr3);
              
            }
        }
    }

    /**
     * 当客户端连接建立时触发的回调函数
     * @param $connection：就是客户端
     */
    public function onConnect($connection)
    {
        $connect_ip=$connection->getRemoteIp();
        if($connect_ip != '192.168.1.10')
        {
            $connection->close("bad ip");
        }
    }
    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        echo "connection close\n";
    }
    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        // 进程启动时设置一个定时器，定时向所有客户端连接发送数据(定时每10秒一次)
        Timer::add(10, function ()use($worker)
        {
            $time_now = time();
            // 遍历当前进程所有的客户端连接，发送当前服务器的时间
            foreach ($worker->connections as $connection)
            {
                if (empty($connection->lastMessageTime))             //有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                {
                    $connection->lastMessageTime = $time_now;
                    continue;
                }
                if ($time_now - $connection->lastMessageTime > 50)  //上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                {
                     $connection->close();
                }
            }
        });

        global $cacheRedis;                                        //创建Redis实例
        $cacheRedis = new Redis();
    }

    /**将接收的数据转化为reids数据，并使用队列存储形成消息队列*/
    public function createRedisList($redisName,$data=[])
    {
        $redis = $redisName->handler();
        /*将接收到的数据存储为hash数据*/
        $global_reveiveId = $redis->incr('global:receiveId');
        $redis->hmset('Receive:reveiveId:'.$global_reveiveId,array('id' =>$global_reveiveId ,'sourceIp' =>$data['sourceIp'] , 'sourceId' =>$data['sourceId'] ,'targetId' =>$data['targetId'] ,'timeime' =>time() ,'content' =>$data['content'] ,));
        /*为每个用户创建一个链表存储数据，如果数据多余1000条将数据转存到globa：store中*/
        $oneUserData ='users:id:'.$data['sourceId'];                                  
        $redis->lpush($oneUserData,$global_reveiveId); //将每个站点上传的数据的上传序号存到每个站点的上传链表中
        $curUserLength = $redis->lLen($oneUserData);   //判断每个站点上传的数据在链表中的长度
        if($curUserLength >100)                        //如果当前站点在内存中数据多于100条，需把数据转存到global：store中
        {
            $redis->rpoplpush($oneUserData,'global:store');
        }     
        $globalAllUsersDataLength = $redis->lLen('global:store');
        if($globalAllUsersDataLength>1000)           //如果存放所有用户上传数据的链表数据大于一定量后，需要将数据存入mysql中
        {
            $this->RedisSaveToMysql($redisName);
        }  
    }

    public function RedisSaveToMysql($redisName)
    {
        $redis = $redisName->handler();    
        $globaldatas      = $redis->lRange('global:store', 0, 10);      //每次提取10个数据索引
        $globaldataLength = $redis->lLen('global:store');
        while ($globaldataLength > 0) 
        {
            try 
            {
                CollectDataModel::startTrans();                     //启动事务
                $redis->watch('global:store');                      //在事务执行之前listKey被其他命令所改动，事务将被打断
                $arrList = [];
                foreach ($globaldatas as $key => $val)              //从内存中取出需要入库的数据
                {

                    $temp_hash_datas = $redis->hmget('Receive:reveiveId:'.$val,array('id','sourceIp','sourceId','targetId','timeime','content'));
                    $arrList[] = array(
                        'sourceIp'   => $temp_hash_datas['sourceIp'], 
                        'sourceId'   => $temp_hash_datas['sourceId'], 
                        'targetId'   => $temp_hash_datas['targetId'],
                        'time'       => $temp_hash_datas['timeime'],
                        'content'    => $temp_hash_datas['content'],
                        'ukey'       => $temp_hash_datas['id'],
                    );
                }
                foreach ($arrList as $key => $value)               //将数据写入mysql中
                {
                    $insertResult = CollectDataModel::create($value);
                    if (!$insertResult) 
                    {
                        CollectDataModel::rollback();                     //回滚事务
                        $result = array("code" => 500, "msg" => "Data Insert into Fail!", 'data' => 'dataLength:' . $dataLength);
                        return json_encode($result);
                    }
                }
                CollectDataModel::commit();                           // 提交事务

                $redis->lTrim('global:store', 11, -1);                // 让列表只保留指第10个到最后一个元素
                $redis->lRange('global:store', 0, 10);
                $globaldataLength = $redis->lLen('global:store');
            } catch (Exception $e) {
                CollectDataModel::rollback();
                $result = array("code" => 500, "msg" => "Data Insert into Fail!", "data" =>"");
                return json_encode($result);
            }
        }
    }
}