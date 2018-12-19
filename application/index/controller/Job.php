<?php
namespace app\index\controller;

use think\Session;
use think\Request;
use think\captcha\Captcha;
use app\index\controller\CommonIndex;
use app\index\model\Job as JobModel;
use app\index\model\Employee as EmployeeModel;
class Job extends CommonIndex
{
	protected $params;
	public function index(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$data = JobModel::all(['status'=>1]);
		if(!empty($data))
		{
			$this->assign('jobList',$data);
		}
		else
		{
			$this->assign('jobList',null);
		}
		return $this->fetch('index');
	}

	public function apply(Request $request)
	{
		$id = $request->param('id');
		$this->assign('navLeftMenuList',null);    //设置左侧菜单
		$this->assign('LocationMenuList',null);   //设置location
		$data = JobModel::get($id);
		if(!empty($data))
		{
			$this->assign('jobone',$data);
		}
		else
		{
			$this->assign('jobone',null);
		}
		return $this->fetch('apply');
	}

	public function join(Request $request)
	{
		if($request->isPost())
		{
			$data = $request->param();
			$this->getIndexNavLeftMenuList($data['pid']);    //设置左侧菜单
			$this->getIndexLocationMenuList($data['pid']);   //设置location
			$captcha = new Captcha();
			$result  = $captcha->check($data['ImgCode']);     //验证码校验
			if($result ===false)
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='验证码错误';
	            $this->params['data']  ='';
			}
			else
			{
				$map = [
					'pid'    => $data['pid'],
					'name'   => $data['name'],
					'sex'    => $data['sex'],
					'born'   => strtotime($data['born']),
					'marry'  => $data['marry'],
					'tel'    => $data['tel'],
					'qq'     => $data['qq'],
					'email'  => $data['email'],
					'school' => $data['school'],
					'degree' => $data['degree'],
					'major'  => $data['major'],
					'endtime'=> strtotime($data['endtime']),
					'address'=> $data['address'],
					'speciality'=> $data['speciality'],
					'work'  => $data['work'],
				];

				$result = EmployeeModel::create($map);
				if($result)
				{
					$this->params['code']  =200;
		            $this->params['msg']   ="添加成功";
		            $this->params['data']  ='';
				}
				else
				{
					$this->params['code']  =400;
		            $this->params['msg']   ="添加失败";
		            $this->params['data']  ='';
				}
			}
		}
		return json($this->params);   
	}
}