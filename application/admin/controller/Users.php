<?php
namespace app\admin\controller;

use think\Session;
use think\Request;
use think\Paginator;
use app\admin\controller\Base;
use app\admin\model\Users as UsersModel;
use app\admin\model\Roles as RolesModel;
class Users extends Base
{
	protected $params;
	
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function index(Request $request)
	{
		global $cacheRedis;                                           //引用全局变量                                 
        $redis   = $cacheRedis->handler();
        $cur_day = $this->getdayNumber(time());
		$controllName = $request->controller();
		$redis->incr('access'.$controllName.$cur_day);                //该控制器的今天的访问次数

		$username = Session::get('user_info.name');
		$role     = Session::get('user_role');
		if($role == 1)
		{
			$list  = UsersModel::paginate(5);
			$page  = $list->render();
			foreach ($list as $value) 
			{
				if(isset($value->Roles->title))
				    $value->role = $value->Roles->title;
				else
					$value->role = "未设置";
			}
			$count = UsersModel::count();

			$this->assign('list',$list);
		    $this->assign('page',$page);
		    $this->assign('count',$count);
		}
		else
		{
			$data  = UsersModel::get(['name'=>$username]);
			$data->role = $role;
	
			$this->assign('user',$data);
			$this->assign('count',1);
		}

		return $this->fetch('admin_list');
	}

	public function create()
	{
		$id = UsersModel::withTrashed()->max('id');
		$id = $id+1;
		$userList = RolesModel::withTrashed()->select();

		$this->assign('id',$id);
		$this->assign('userList',$userList);

		return $this->fetch('admin_add');
	}

	public function add(Request $request)
	{
		if($request->isPost())
		{
			$data = $request->param();
			$id   = $data['adminId'];
			$map  = ['id'=>$id,'name'=>$data['adminName'],'password'=>$data['adminPassword'],'phone'=>$data['adminPhone'],'email'=>$data['adminEmail'],'status'=>$data['adminStatus'],'role_id'=>$data['adminRole']];
			$result = UsersModel::create($map);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='添加失败';
	            $this->params['data']  ='';
			}
			else
			{
	            $this->params['code']  =200;
	            $this->params['msg']   ='添加成功';
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

	public function setStatus(Request $request)
	{
		if($request->isGet())
		{
			$id     = $request->param('id');
			$result = UsersModel::get($id);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				if($result['name'] == 'admin')
				{
					$this->params['code']  =400;
		            $this->params['msg']   ='此账号不能停用';
		            $this->params['data']  ='';
				}
				else
				{
					if($result->getData('status') == 1)
					{
						UsersModel::update(['status'=>0],['id'=>$id]);
					}
					else
					{
						UsersModel::update(['status'=>1],['id'=>$id]);
					}

					$this->params['code']  =200;
		            $this->params['msg']   ='修改成功';
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

	public function edit(Request $request)
	{
		$id = $request->param('id');
		if(is_null($id))
		{
			$this->params['code']  =400;
            $this->params['msg']   ='数据获取失败';
            $this->params['data']  ='';
            return json($this->params);
		}
		else
		{
			$result = UsersModel::get($id);
			$roles  = RolesModel::all();

			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='数据获取失败';
	            $this->params['data']  ='';
	            return json($this->params);
			}
			else
			{
				$this->assign('data',$result);
				$this->assign('roles',$roles);

				return $this->fetch('admin_edit');
			}
		}
	}

	public function save(Request $request)
	{
		if($request->isPost())
		{   
			$id     = $request->param('adminId');
			$data   = $request->param();
			if($id==1)
			{
				$data['adminName'] = 'admin';
			}
			$result = UsersModel::get(['id'=>$id]);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='数据查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				$map=['id'=>$id,'name'=>$data['adminName'],'password'=>$data['adminPassword'],'phone'=>$data['adminPhone'],'email'=>$data['adminEmail'],'status'=>$data['adminStatus'],'role_id'=>$data['adminRole'],'update_time'=>time()];
				$result=UsersModel::where(['id'=>$id])->update($map);
				if(is_null($result))
				{
					$this->params['code']  =400;
		            $this->params['msg']   ='更新失败';
		            $this->params['data']  ='';
				}
				else
				{
					$this->params['code']  =200;
		            $this->params['msg']   ='更新成功';
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

	public function checkUserName(Request $request)
	{
		if($request->isGet())
		{
			$username = trim($request->param('name')); //移除字符串两侧的空白字符或其他预定义字符
			$name     = UsersModel::get(['name' =>$username]);
			if(is_null($name))
			{
				$this->params['code']  =200;
	            $this->params['msg']   ='用户名可用';
	            $this->params['data']  ='';
			}
			else
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='用户名被占用';
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

	public function checkPhone(Request $request)
	{
		if($request->isGet())
		{
			$userphone = trim($request->param('phone')); //移除字符串两侧的空白字符或其他预定义字符
			$phone     = UsersModel::get(['phone' =>$userphone]);
			if(is_null($phone))
			{
				$this->params['code']  =200;
	            $this->params['msg']   ='手机号可用';
	            $this->params['data']  ='';
			}
			else
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='手机号已被占用';
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

	public function checkEmail(Request $request)
	{
		if($request->isGet())
		{
			$useremail = trim($request->param('email')); //移除字符串两侧的空白字符或其他预定义字符
			$email     = UsersModel::get(['email' =>$useremail]);
			if(is_null($email))
			{
				$this->params['code']  =200;
	            $this->params['msg']   ='邮箱可用';
	            $this->params['data']  ='';
			}
			else
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='邮箱被占用';
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
  			$result = UsersModel::get($id);
  			if($result['is_delete'] == 0)
  			{
  				$this->params['code']  =400;
	            $this->params['msg']   ='不能删除该用户';
	            $this->params['data']  ='';
  			}
  			else
  			{
  				UsersModel::destroy($id);
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

	public function reset()
	{
		$datas = UsersModel::onlyTrashed()->find();    //仅仅需要查询软删除的数据
		if(is_null($datas))	
		{
			$this->params['code']  =200;
            $this->params['msg']   ='无数据需要恢复';
            $this->params['data']  ='';
		}
		else
		{
			$result = UsersModel::update(['delete_time'=>NULL],['is_delete'=>1]);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='恢复错误';
	            $this->params['data']  ='';
			}
			else
			{
				$this->params['code']  =200;
	            $this->params['msg']   ='恢复成功';
	            $this->params['data']  ='';
			}
		}
		return json($this->params);
	}
	
	public function console_log($data)
    {
        if (is_array($data) || is_object($data))
        {
            echo("<script>console.log('".json_encode($data)."');</script>");
        }
        else
        {
            echo("<script>console.log('".$data."');</script>");
        }
    }
}