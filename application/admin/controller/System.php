<?php
namespace app\admin\controller;

use think\Cache;
use think\Session;
use think\Request;
use think\Paginator;
use think\Collection;
use app\admin\controller\Base;
use app\admin\model\Roles as RolesModel;
use app\admin\model\Menus as MenusModel;
use app\admin\model\Permission as PermissionModel;

class System extends Base
{
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function index()
	{
		$list  = MenusModel::getCate();
		$count = MenusModel::count();

		$this->assign('cate',$list);
		$this->assign('count',$count);

		return $this->fetch('system_category');
	}

	public function create()
	{
		$list   = MenusModel::getCate();
		$levels = MenusModel::getMidAndLevel();
		$allow  = PermissionModel::all();
//		dump($levels);
		$this->assign('cate',$list);
		$this->assign('levels',json_encode($levels));
		$this->assign('allow',$allow);
		return $this->fetch('system_category_add');
	}

	public function add(Request $request)
	{
		if($request->isPost())
		{
			$data = $request->param();
			if($data['catePid'] == '-1')             //用户需要添加顶级菜单项
			{
				$order =MenusModel::withTrashed()->where('pid',0)->count();
				$map   =[
					'pid'        => 0,
					'order'      => $order+1,
					'title'      => $data['cateName'],
					'pic'        => $data['catePic'],
					'controller' => 0,
					'method'     => 0,
					'ishidden'   => $data['cateIshidden'],
					'status'     => $data['cateStatus']
				];
                $result    = MenusModel::create($map);
                if(is_null($result))
                {
                	$this->params['code']  =400;
		            $this->params['msg']   ='新增失败';
		            $this->params['data']  ='';
                }
                else
                {
                	$this->params['code']  =200;
		            $this->params['msg']   ='新增成功';
		            $this->params['data']  ='';
                }
			}
			else
			{
				$tempdata = MenusModel::get($data['catePid']);
				$temppid  = $tempdata['mid'];   
				$order    = MenusModel::withTrashed()->where('pid',$temppid)->count(); 
				$map      = [
					'pid'        => $temppid,
					'order'      => $order+1,
					'title'      => empty($data['cateName'])?$data['cateAllow']:$data['cateName'],
					'pic'        => '',
					'controller' => $data['cateController'],
					'method'     => $data['cateMethod'],
					'ishidden'   => $data['cateIshidden'],
					'status'     => $data['cateStatus']
				];
				$result    = MenusModel::create($map);
                if(is_null($result))
                {
                	$this->params['code']  =400;
		            $this->params['msg']   ='新增失败';
		            $this->params['data']  ='';
                }
                else
                {
                	$this->params['code']  =200;
		            $this->params['msg']   ='新增成功';
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
			$result = MenusModel::get($id);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				$mids = MenusModel::findItem($result['mid']);                 //需要锁定/解锁的数据项mid集合
				if($result->getData('status') == 1)
				{
					MenusModel::update(['status'=>0],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						MenusModel::update(['status'=>0],['mid'=>$mids[$i]]);
					}
				}
				else
				{
					MenusModel::update(['status'=>1],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						MenusModel::update(['status'=>1],['mid'=>$mids[$i]]);
					}
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

	public function setIshidden(Request $request)
	{
		if($request->isGet())
		{
			$id     = $request->param('id');
			$result = MenusModel::get($id);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				$mids = MenusModel::findItem($result['mid']);                 //需要锁定/解锁的数据项mid集合
				if($result->getData('ishidden') == 1)
				{
					MenusModel::update(['ishidden'=>0],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						MenusModel::update(['ishidden'=>0],['mid'=>$mids[$i]]);
					}
				}
				else
				{
					MenusModel::update(['ishidden'=>1],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						MenusModel::update(['ishidden'=>1],['mid'=>$mids[$i]]);
					}
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
  			$result = MenusModel::get($id);
  			if($result['is_delete'] == 0)
  			{
  				$this->params['code']  =400;
	            $this->params['msg']   ='不能删除该菜单项';
	            $this->params['data']  ='';
  			}
  			else
  			{
  				MenusModel::destroy($id);                                     //删除父菜单项
  				$mids = MenusModel::findItem($result['mid']);                 //需要删除的数据项mid集合
  				for($i=0;$i<count($mids);$i++)                                //删除子菜单项
				{
					MenusModel::destroy($mids[$i]);
				}
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
		$datas = MenusModel::onlyTrashed()->find();    //仅仅需要查询软删除的数据
		if(is_null($datas))	
		{
			$this->params['code']  =200;
            $this->params['msg']   ='无数据需要恢复';
            $this->params['data']  ='';
		}
		else
		{
			$result = MenusModel::update(['delete_time'=>NULL],['is_delete'=>1]);
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

	public function edit(Request $request)
	{
		$mid    = $request->param('id');
		$data   = MenusModel::get($mid);             //取出当前节点的数据
		if($data['pid'] == 0)
		{
			$fdata = '顶级菜单项';
		}
		else
		{
			$fdatas  = MenusModel::get($data['pid']);     //取出父节点的数据
			$fdata   = $fdatas['title'];
		}
		

		$this->assign('ftitle',$fdata);
		$this->assign('list',$data);

		return $this->fetch('system_category_edit');
	}

	public function save(Request $request)
	{
		if($request->isPost())
		{   
			$tempdata         = $request->except('cateFid');
			$id               = $tempdata['cateMid'];
			$title            = $tempdata['cateName'];
			if($title =="")
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='提交数据不完整';
	            $this->params['data']  ='';
			}
			else
			{
				$map    = ['title'=>$tempdata['cateName'],'pic'=>$tempdata['catePic'],'controller'=>$tempdata['cateController'],'method'=>$tempdata['cateMethod'],'ishidden'=>$tempdata['cateIshidden'],'status'=>$tempdata['cateStatus']];
				$result = MenusModel::update($map,['mid'=>$id]);
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

	public function resetcache()
	{
		$userRole = Session::get('user_role');
		if(Cache::get('user_menu'.$userRole))                              //设置菜单项
		{
			$this->showAllRolesMenuList();
			$curMenuList = Cache::get('user_menu'.$userRole);
			Session::set('user_menu',$curMenuList);

			$this->params['code']  =200;
            $this->params['msg']   ='数据更新成功';
            $this->params['data']  ='';
		}
		else
		{
			$this->params['code']  =400;
            $this->params['msg']   ='数据未更新';
            $this->params['data']  ='';
		}
		return json($this->params);
	}

	public function showAllRolesMenuList()
	{
		$list     = MenusModel::getMenuDetailList();
		$MenuList = $this->CateTwoLevelMenuDetail($list);
		$datas    = RolesModel::all();
		foreach ($datas as $key => $value1) 
		{
			$rights   = $value1['rights'];
			$arr      = explode(',', $rights);                       //将字符串以,分割为一个一维数组
			$count    = count($arr);
	        $newCheckedMenus = array();
	        foreach ($MenuList as  $value)                          //根据数据库中权限给菜单添加是否选中属性
			{
				for($t=0;$t<$count;$t++)
				{
					if($value['father']['id'] == (int)$arr[$t])
					{
						$value['father']['checked'] = 1;
						break;
					}
					else
					{
						$value['father']['checked'] = 0;
					}
				}

	            for($k=0;$k<count($value['son']);$k++)
	            {
	            	for($t=0;$t<$count;$t++)
					{
						if($value['son'][$k]['id'] == (int)$arr[$t])
						{
							$value['son'][$k]['checked'] = 1;
							break;
						}
						else
						{
							$value['son'][$k]['checked'] = 0;
						}
					}
	            }
	            $newCheckedMenus[] = $value;
			}
            $userRole = $value1['role'];
			Cache::set('user_menu'.$userRole,$newCheckedMenus);
		}
	}
	//仅获取两级菜单，也就是说本系统仅支持两级菜单，第三级为权限菜单
	public function CateTwoLevelMenuDetail($arr)
	{
		$k=0;
		$Fmenu=array();                          //一级菜单
		$Omenu=array();                          //其他级菜单
		$TempOmenu=array();
		$ResultMenu=array();
		$tempMenu = array();

		foreach ($arr as $key => $value) 
		{
			if($value['level'] == 1)
			{
				if(!empty($Fmenu))
				{
					$tempMenu['father']  = $Fmenu;
					$tempMenu['son']     = $TempOmenu;
					$ResultMenu[$k]      = $tempMenu;
					$k++;
				}
				array_splice($tempMenu, 0, count($tempMenu));
				array_splice($Fmenu,    0, count($Fmenu));
				array_splice($Omenu,    0, count($Omenu));
				array_splice($TempOmenu,0, count($TempOmenu));
				$Fmenu['id']         = $value['mid'];
				$Fmenu['name']       = $value['title'];
				$Fmenu['pic']        = $value['pic'];
				$Fmenu['hidden']     = $value['ishidden'];
				$Fmenu['status']     = $value['status'];
			}
			elseif ($value['level'] == 2) 
			{
				$Omenu['id']         = $value['mid'];
				$Omenu['name']       = $value['title'];
				$Omenu['controller'] = $value['controller'];
				$Omenu['method']     = $value['method'];
				$Omenu['hidden']     = $value['ishidden'];
				$Omenu['status']     = $value['status'];
				$TempOmenu[]         = $Omenu;
			}
		}

		if(!empty($Fmenu))
		{
			$tempMenu['father'] = $Fmenu;
			$tempMenu['son']    = $TempOmenu;
			$ResultMenu[$k] = $tempMenu;
		}

		return Collection::make($ResultMenu)->toArray(); 
	}


}