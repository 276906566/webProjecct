<?php
namespace app\admin\model;

use think\Model;
use think\Collection;
use traits\model\SoftDelete;

class Menus extends Model
{
    protected $updateTime = false;
    protected $createTime = false;

    //软删除
    use SoftDelete;                                                   //导入软删除方法集
    protected $deleteTime = 'delete_time';
    
	//创建一个静态方法getCategory来获取分类信息(将查询方法创建在模型中，尽可能使用静态方法，提高执行效率)
    public static function getCate($pid=0,&$result=[],$blank=0)
    {
    	$res=self::all(['pid'=>$pid]); 
        $blank +=2;                                                                                                   
    	foreach($res as $key=>$value)                                               //遍历分类表
    	{
    		$value->cate_title = str_repeat('&nbsp;&nbsp;',$blank).'|--'.$value->title;   //自定义分类名称的显示格式
    		$result[]=$value;                                                       //将查询到的当前记录保存到结果数组中$result
    		self::getCate($value->mid,$result,$blank);                              //将当前记录的ID作为下一级分类的父ID，继续递归调用本方法
    	}
    	return Collection::make($result)->toArray();                                //返回查询结果，调用结果集类make方法打包当前结果，转为二维数组返回
    }

    public static function getMidAndLevel($pid=0,$level=0,&$result=[])
    {
        $arr   = array();
        $res=self::all(['pid'=>$pid]);    
        $level = $level+1;                                                                                              
        foreach($res as $key=>$value)                                               //遍历分类表
        {
            $arr['Mmid']   = $value->mid;
            $arr['Mlevel'] = $level;  
            $result[]      = $arr;                                                  //将查询到的当前记录保存到结果数组中$result
            self::getMidAndLevel($value->mid,$level,$result);                       //将当前记录的ID作为下一级分类的父ID，继续递归调用本方法
        }
        return Collection::make($result)->toArray();                                //返回查询结果，调用结果集类make方法打包当前结果，转为二维数组返回
    }
    //创建一个静态方法findItem来获得单个菜单项目下所有的菜单的mid值
    public static function findItem($mid=0,&$result=[])
    {
        $res = self::all(['pid'=>$mid]);
        foreach ($res as $key => $value) 
        {
           $value->tempmid = $value->mid;
           $result[]       = $value->tempmid;
           self::findItem($value->mid,$result);
        }
        return Collection::make($result)->toArray();
    }

    //创建一个静态方法,遍历所有菜单，将每级菜单添加level编号，一级菜单level=1，二级菜单level=2，……
    public static function getMenuList($pid=0,$level=0,&$result=[])
    {
        $arr   = array();
        $res=self::all(['pid'=>$pid]);    
        $level = $level+1;                                                                                              
        foreach($res as $key=>$value)                                               //遍历分类表
        {
            $arr['Mpid']   = $value->pid;
            $arr['Mmid']   = $value->mid;
            $arr['Mtitle'] = $value->title; 
            $arr['Mlevel'] = $level;  
            $result[]      = $arr;                                                  //将查询到的当前记录保存到结果数组中$result
            self::getMenuList($value->mid,$level,$result);                          //将当前记录的ID作为下一级分类的父ID，继续递归调用本方法
        }
        return Collection::make($result)->toArray();                                //返回查询结果，调用结果集类make方法打包当前结果，转为二维数组返回
    }

    //创建一个静态方法getCategory来获取分类信息(将查询方法创建在模型中，尽可能使用静态方法，提高执行效率)
    public static function getMenuDetailList($pid=0,$level=0,&$result=[])
    {
        $arr = array();
        $res=self::all(['pid'=>$pid]);  
        $level = $level+1;                                                                                                  
        foreach($res as $key=>$value)                                               //遍历分类表
        {
            $arr['pid']        = $value->pid;
            $arr['mid']        = $value->mid;
            $arr['title']      = $value->title;   
            $arr['pic']        = $value->pic; 
            $arr['controller'] = $value->controller; 
            $arr['method']     = $value->method; 
            $arr['ishidden']   = $value->ishidden; 
            $arr['status']     = $value->status; 
            $arr['level']      = $level;  
            $result[]=$arr;                                                         //将查询到的当前记录保存到结果数组中$result
            self::getMenuDetailList($value->mid,$level,$result);                           //将当前记录的ID作为下一级分类的父ID，继续递归调用本方法
        }
        return Collection::make($result)->toArray();                                //返回查询结果，调用结果集类make方法打包当前结果，转为二维数组返回
    }

    public function getStatusAttr($value)
    {
    	$status = [0=>'解锁',1=>'锁定'];
        return $status[$value];
    }

    public function getIshiddenAttr($value)
    {
    	$status = [0=>'显示',1=>'隐藏'];
        return $status[$value];
    }
}