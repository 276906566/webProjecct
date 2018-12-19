<?php
namespace app\admin\model;

use think\Model;
use think\Collection;
use traits\model\SoftDelete;

class ProductCate extends Model
{
//	protected $table      = 'product_cate';          // 设置当前模型对应的完整数据表名称
    protected $updateTime = false;
    protected $createTime = false;

    //软删除
    use SoftDelete;                                  //导入软删除方法集
    protected $deleteTime = 'delete_time';

	//创建一个静态方法getCategory来获取分类信息(将查询方法创建在模型中，尽可能使用静态方法，提高执行效率)
    public static function getCate($pid=0,&$result=[],$blank=0)
    {
        $res=self::all(['pid'=>$pid]); 
        $blank +=2;                                                                                                   
        foreach($res as $key=>$value)                                               //遍历分类表
        {
            $value->cate_title = str_repeat('&nbsp;&nbsp;',$blank).'|--'.$value['name'];   //自定义分类名称的显示格式
            $result[]=$value;                                                       //将查询到的当前记录保存到结果数组中$result
            self::getCate($value->mid,$result,$blank);                              //将当前记录的ID作为下一级分类的父ID，继续递归调用本方法
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