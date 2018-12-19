<?php
namespace app\admin\model;

use think\Model;

class Mood extends Model
{
	protected $deleteTime = false;
    protected $autoWriteTimestamp = true;
    protected $updateTime = true;
    protected $createTime = false;
}