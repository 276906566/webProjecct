<?php
namespace app\admin\controller;

use think\Request;
use think\Session;
use app\admin\controller\Base;
use app\admin\model\Job as JobModel;
use app\admin\model\LabelCate as LabelCateModel;

class Job extends Base
{
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function index()
	{
		$datas = JobModel::all();
		$count = JobModel::count();

		$this->assign('joblist',$datas);
		$this->assign('count',$count);
		
		return $this->fetch('job_list');
	}

	public function add(Request $request)
	{
		return $this->fetch('job_add');
	}

	public function save()
    {
    	if($this->request->isPost())
		{
			$data    = $this->request->param(true);
			if(!empty($data))
			{
				$data['status']        = 0;
				$result                = JobModel::create($data);
				if(is_null($result))
				{
					$this->params['code']  =400;
		            $this->params['msg']   ="数据写入失败";
		            $this->params['data']  ='';
				}
				else
				{
					$this->params['code']  =200;
		            $this->params['msg']   ="数据写入成功";
		            $this->params['data']  ='';
				}
				
			}
			else
			{
				$this->params['code']  =400;
	            $this->params['msg']   ="数据返回错误";
	            $this->params['data']  ='';
			}
		}
		else
		{
			$this->params['code']  =400;
            $this->params['msg']   ="请求出错";
            $this->params['data']  ='';
		}

		return json($this->params);   
    }

    public function edit(Request $request)
    {
    	$id      = $request->param('id');
    	$data    = JobModel::get(['id'=>$id]);

		$this->assign('data',$data);

    	return $this->fetch('job_edit');
    }

    public function update(Request $request)
	{
		if($request->isPost())
		{   
			$tempdata         = $request->param();
			if(empty($tempdata))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='提交数据错误';
	            $this->params['data']  ='';
			}
			else
			{
				$result = JobModel::update($tempdata,['id'=>$tempdata['id']]);
				if(!is_null($result))
				{
					$this->params['code']  =200;
		            $this->params['msg']   ='数据更新成功';
		            $this->params['data']  ='';
				}
				else
				{
					$this->params['code']  =400;
		            $this->params['msg']   ='数据更新失败';
		            $this->params['data']  ='';
				}
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

	public function setStatus(Request $request)
	{
		if($request->isGet())
		{
			$id     = $request->param('id');
			$result = JobModel::get($id);
			if(empty($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				if($result->getData('status') == 1)
				{
					JobModel::update(['status'=>0],['id'=>$id]);
				}
				else
				{
					JobModel::update(['status'=>1],['id'=>$id]);
				}

				$this->params['code']  =200;
	            $this->params['msg']   ='修改成功';
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

    public function delete(Request $request)
	{
		if($request->isGet())
		{
  			$id = $request->param('id');
  			$result = JobModel::get($id);

  			if($result['is_delete']== 0)
  			{
  				$this->params['code']  =400;
	            $this->params['msg']   ='不能删除该职位';
	            $this->params['data']  ='';
  			}
  			else
  			{
  				JobModel::destroy($id,true);   //真实删除
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