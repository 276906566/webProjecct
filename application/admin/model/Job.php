<?php
namespace app\admin\model;

use think\Model;

class Job extends Model
{
	protected $deleteTime = false;
    protected $autoWriteTimestamp = true;
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    public function getStatusAttr($value)
    {
        $status = [0=>'未发布',1=>'已发布'];
        return $status[$value];
    }

    public function getTypeAttr($value)
    {
        $status = [0=>'兼职',1=>'全职'];
        return $status[$value];
    }

    public function getDegreeAttr($value)
    {
        $status = [0=>'高中',1=>'中专',2=>'大专',3=>'本科',4=>'研究生',5=>'博士研究生'];
        return $status[$value];
    }
}