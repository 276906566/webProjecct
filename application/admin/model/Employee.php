<?php
namespace app\admin\model;

use think\Model;

class Employee extends Model
{
	protected $deleteTime = false;
    protected $autoWriteTimestamp = false;
    protected $updateTime = false;
    protected $createTime = false;

    public function getDegreeAttr($value)
    {
        $status = [0=>'高中',1=>'中专',2=>'大专',3=>'本科',4=>'研究生',5=>'博士研究生'];
        return $status[$value];
    }
}