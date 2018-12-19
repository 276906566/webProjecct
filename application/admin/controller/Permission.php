<?php
namespace app\admin\controller;

use think\Request;
use app\admin\controller\Base;
use app\admin\model\Permission as PermissionModel;

class Permission extends Base
{
	protected $params;
	
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}

	public function index()
	{
		$count = PermissionModel::count();
		$data  = PermissionModel::all();

		$this->assign('count',$count);
		$this->assign('list',$data);
		
		return $this->fetch('admin_permission');
	}	

	public function add()
	{
		return $this->fetch('permission_add');
	}

	public function save(Request $request)
	{
		if($request->isPost())
		{
			$data   = $request->param();
			$result = PermissionModel::create($data);
			if($result)
			{
				$this->params['code']  =200;
	            $this->params['msg']   ='添加成功';
	            $this->params['data']  ='';
			}
			else
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='添加失败';
	            $this->params['data']  ='';
			}
		}
		else
		{
			$this->params['code']  =400;
            $this->params['msg']   ='请求类型错误';
            $this->params['data']  ='';
		}
		return json($this->params);
	}

}