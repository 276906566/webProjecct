<?php
namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Users extends Model
{
    //软删除
	use SoftDelete;                                                   //导入软删除方法集
	protected $deleteTime = 'delete_time';
    //时间戳
    protected $autoWriteTimestamp = true;
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';
    //保存数据时自动完成
    protected $auto = [
        'delete_time' =>NULL,
        'is_delete'   =>1,                                            //1：允许删除  0：禁止删除
    ];
    //新增数据时自动完成
    protected $insert = [
        'login_time' => 0,
        'login_count'=> 0,
    ];
    //更新时自动完成
    protected $update = [
    ];

	public function getStatusAttr($value)
    {
        $status = [0=>'已禁用',1=>'已启用',2=>'待审核',3=>'删除'];
        return $status[$value];
    }

    public function Roles()
    {
        //belongsTo('关联表模型名','主表的主键名','关联表的外键名',['模型别名定义'],'join类型');
        return $this->belongsTo('Roles','role_id','rid');
    }
}