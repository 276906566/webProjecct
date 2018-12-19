<?php
namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Roles extends Model
{
	//软删除
	use SoftDelete;                                                   //导入软删除方法集
	protected $deleteTime = 'delete_time';
	
	 //时间戳
    protected $autoWriteTimestamp = true;
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';
    
	public function Users()
	{
		//hasMany('关联表User模型名'，'关联表User的外键名'，'主表Roles的主键名'，['模型别名'])
		return $this->hasMany('Users','role_id','rid');
	}
}

		