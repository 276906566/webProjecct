<?php
namespace app\index\model;

use think\Model;

class Employee extends Model
{
	protected $deleteTime = false;
    protected $autoWriteTimestamp = false;
    protected $updateTime = false;
    protected $createTime = false;
}