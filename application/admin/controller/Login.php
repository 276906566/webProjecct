<?php
namespace app\admin\controller;

use think\Request;
use think\Session;
use think\captcha\Captcha;
use app\admin\controller\Base;
use app\admin\model\Login as LoginModel;
use app\admin\model\Users as UsersModel;

class Login extends Base
{
	protected $rule = [
       'name|姓名'      => 'require',
       'password|密码'  => 'require',
       'captcha|验证码' => 'require|captcha',
	];

	protected $params;
/*	
	function __construct(argument)
	{
		# code...
	}
*/
	public function index()
	{
		$this->assign('title','后台管理系统');
		$this->assign('keywords','后台管理系统');
		$this->assign('description','后台管理系统');
		$this->assign('copyRight','华气厚普');
		$this->logined();
		
		return $this->fetch('login');
	}

	public function check(Request $request)
	{
		if($request->isPost())
		{
			$data    = $request->param(true);                       //获取数据

			$captcha = new Captcha();
			$result  = $captcha->check($data['login_captcha']);     //验证码校验
			if($result ===false)
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='验证码错误';
	            $this->params['data']  ='';
			}
			else
			{
				$map = ['name' => $data['login_name']];               //设置查询条件
				$result = LoginModel::get($map);                      //获取查询数据
				if(is_null($result))
				{
					$this->params['code']  =400;
		            $this->params['msg']   ='用户不存在';
		            $this->params['data']  ='';
				}
				else
				{
					if($data['login_password'] == $result['password'])
					{
						$this->params['code']  =200;
			            $this->params['msg']   ='登陆成功';
			            $this->params['data']  ='';

			            $result->setInc('login_count');
			            LoginModel::where('id',$result->id)->update(['login_time' => time()]);
			            //读取Users表中的当前用户在关联表Roles中的role字段，作为登陆角色
			            $user_role = UsersModel::get($result->id)->Roles->role;

			            Session::set('user_role',$user_role);
			            Session::set('user_id',$result->id);
			            Session::set('user_info',$result->getData());
					}
					else
					{
						$this->params['code']  =400;
			            $this->params['msg']   ='密码不正确';
			            $this->params['data']  ='';
					}
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

	public function loginout()
	{
		Session::delete('user_id');
		Session::delete('user_info');
		Session::delete('user_role');
		Session::delete('user_menu');

		$this->assign('title','后台管理系统');
		$this->assign('keywords','后台管理系统');
		$this->assign('description','后台管理系统');
		$this->assign('copyRight','华气厚普');

		return $this->fetch('login');
	}
}