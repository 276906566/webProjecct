<?php
namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Feedback extends Model
{
	use SoftDelete;                                                   //导入软删除方法集
	protected $deleteTime = 'delete_time';
	
    protected $autoWriteTimestamp = true;
    protected $updateTime = 'update_time';
    protected $createTime = false;
    
}