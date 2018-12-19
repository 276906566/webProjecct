<?php
namespace app\admin\controller;

use think\Request;
use think\Session;
use app\admin\controller\Base;
use app\admin\model\Job as JobModel;
use app\admin\model\Employee as EmployeeModel;

class Employee extends Base
{
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function index()
	{
		$datas = EmployeeModel::all();
		$count = EmployeeModel::count();

		foreach ($datas as $key => $value) 
		{
			$jobData = JobModel::get($value['pid']);
			$value['position'] = $jobData['position'];
		}
		$this->assign('employeelist',$datas);
		$this->assign('count',$count);
		
		return $this->fetch('employee_list');
	}

	public function detail(Request $request)
	{
		$id   = $request->param('id');
		$data = EmployeeModel::get($id);

		$this->assign('data',$data);

		return $this->fetch('employee_detial');
	}

	public function delete(Request $request)
	{
		if($request->isGet())
		{
  			$id = $request->param('id');
  			$result = EmployeeModel::get($id);

  			if($result['is_delete']== 0)
  			{
  				$this->params['code']  =400;
	            $this->params['msg']   ='不能删除该应征者';
	            $this->params['data']  ='';
  			}
  			else
  			{
  				EmployeeModel::destroy($id,true);   //真实删除
  				$this->params['code']  =200;
	            $this->params['msg']   ='删除成功';
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