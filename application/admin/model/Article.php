<?php
namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Article extends Model
{
	use SoftDelete;                                                   //导入软删除方法集
	protected $deleteTime = 'delete_time';
	
    protected $autoWriteTimestamp = true;
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    public function getStatusAttr($value)
    {
        $status = [0=>'已下架',1=>'已发布'];
        return $status[$value];
    }
}
