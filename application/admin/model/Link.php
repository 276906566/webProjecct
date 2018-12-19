<?php
namespace app\admin\model;

use think\Model;
use think\Collection;

class Link extends Model
{
	protected $updateTime = false;
    protected $createTime = false;

    public function getTypeidAttr($value)
    {
        $status = [0=>'默认分类',1=>'其他'];
        return $status[$value];
    }

    public function getLinktypeAttr($value)
    {
        $status = [0=>'logo链接',1=>'文字链接'];
        return $status[$value];
    }

    public function getPassAttr($value)
    {
        $status = [0=>'待审核',1=>'通过',2=>'已发布',3=>'已下架',4=>'不通过'];
        return $status[$value];
    }
}