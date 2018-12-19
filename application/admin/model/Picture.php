<?php
namespace app\admin\model;

use think\Model;

class Picture extends Model
{
	protected $updateTime = false;
    protected $createTime = false;

    public static function unlink($path)
    {
        return  is_file($path) && unlink($path);
    }
}