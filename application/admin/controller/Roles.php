<?php
namespace app\admin\controller;

use think\Request;
use think\Paginator;
use think\Collection;
use app\admin\controller\Base;
use app\admin\model\Roles as RolesModel;
use app\admin\model\Menus as MenusModel;

class Roles extends Base
{
	protected $params;
	
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function index()
	{
		$num = 1;
		$count  = RolesModel::count();                            //从Roles表中取出权限种类数
		for($i=1;$i<$count+1;$i++)                                //从users表中取出具有相同权限下的用户列表
		{
			if(implode('',$this->findSameRoleUsers($num)) == "delete")
			{
				$num++;
				$i--;
			}
			else
			{
				$admins[$i] = $this->findSameRoleUsers($num);
				$num++;
			}
		}
		$roles = RolesModel::paginate(8);
		foreach ($roles as $key => $value)             //将有相同权限的用户表字段附给每条记录
		{
			$value->admins = $admins[$key+1];
		}

		$this->assign('list',$roles);
		$this->assign('count',$count);

		return $this->fetch('admin_role');
	}	

	public function findSameRoleUsers($role=1,$result=[])
	{
		$res   = RolesModel::get(['rid'=>$role]);
		$users = isset($res->Users) ? $res->Users : NULL ;

		if(is_array($users))
		{
			foreach ($users as $key => $value) 
			{
				$result[] = $value->name; 
			}
		}
		else
		{
			$result[]= "delete"; 
		}

		return Collection::make($result)->toArray();
	}

	public function add()
	{
		$lists = MenusModel::getMenuList();                   //获取Menus表中的菜单列表

		$MenuList = $this->CateSameMenu($lists);
//		dump($MenuList);
		$this->assign('list',$MenuList);

		return $this->fetch('admin_role_add');
	}

	public function save(Request $request)
	{
		if($request->isPost())
		{
			$data  = $request->param();
			$count = RolesModel::withTrashed()->max('role');
			$map   = [
				'role'   => $count+1,
				'title'  => $data['roleName'],
				'rights' => $data["father_checkboxs"].','.$data["son_checkboxs"].','.$data["allow_checkboxs"],
			];
			$result = RolesModel::create($map);
			if(!is_null($result))
			{
				$this->params['code']  =200;
	            $this->params['msg']   ='添加成功';
	            $this->params['data']  ='';
			}
			else
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='添加错误';
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

	//将同一层级的菜单项目放在一个数组内，便于前台显示
	public function CateSameMenu($arr)
	{
		$k=0;
		$Fmenu=array();                          //一级菜单
		$Smenu=array();                          //二级菜单
		$Omenu=array();                          //其他级菜单
		$TempSmenu=array();
		$TempOmenu=array();
		$ResultMenu=array();
		$tempMenu = array();

		foreach ($arr as $key => $value) 
		{
			if($value['Mlevel'] == 1)
			{
				if(!empty($Fmenu))
				{
					if(!empty($TempOmenu))
						$Smenu['allow']  = $TempOmenu;
					$TempSmenu[]         = $Smenu;
					$tempMenu['father']  = $Fmenu;
					$tempMenu['son']     = $TempSmenu;
					$ResultMenu[$k]      = $tempMenu;
					$k++;
				}
				array_splice($tempMenu, 0, count($tempMenu));
				array_splice($Fmenu,    0, count($Fmenu));
				array_splice($Smenu,    0, count($Smenu));
				array_splice($Omenu,    0, count($Omenu));
				array_splice($TempSmenu,0, count($TempSmenu));
				array_splice($TempOmenu,0, count($TempOmenu));
				$Fmenu['id']   = $value['Mmid'];
				$Fmenu['name'] = $value['Mtitle'];
			}
			else
			{
				if($value['Mlevel'] == 2)
				{
					if(!empty($TempOmenu))
					{
						$Smenu['allow'] = $TempOmenu;
						$TempSmenu[]    = $Smenu;
						array_splice($Smenu, 0, count($Smenu));
						array_splice($Omenu, 0, count($Omenu));
						array_splice($TempOmenu,0, count($TempOmenu));
					}
					if(!empty($Smenu))
					{
						$TempSmenu[] = $Smenu;
					}
					$Smenu['id']    = $value['Mmid'];
					$Smenu['name']  = $value['Mtitle'];
					$Smenu['allow'] = '';
				}
				else
				{
					$Omenu['id']   = $value['Mmid'];
					$Omenu['name'] = $value['Mtitle'];
					$TempOmenu[]   = $Omenu;
				}
				
			}
		}
		if(!empty($Fmenu))
		{
			if(!empty($TempOmenu))
				$Smenu['allow']  = $TempOmenu;
			$TempSmenu[]         = $Smenu;
			$tempMenu['father']  = $Fmenu;
			$tempMenu['son']     = $TempSmenu;
			$ResultMenu[$k]      = $tempMenu;
			$k++;
		}

		return Collection::make($ResultMenu)->toArray(); 
	}

	public function delete(Request $request)
	{
		if($request->isGet())
		{
  			$id = $request->param('id');
  			$result = RolesModel::get($id);

  			if($result['is_delete']== 0)
  			{
  				$this->params['code']  =400;
	            $this->params['msg']   ='不能删除该角色';
	            $this->params['data']  ='';
  			}
  			else
  			{
  				RolesModel::destroy($id);
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
		$datas = RolesModel::onlyTrashed()->find();    //仅仅需要查询软删除的数据
		if(is_null($datas))	
		{
			$this->params['code']  =200;
            $this->params['msg']   ='无数据需要恢复';
            $this->params['data']  ='';
		}
		else
		{
			$result = RolesModel::update(['delete_time'=>NULL],['is_delete'=>1]);
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
		$id     = $request->param('id');
		$data   = RolesModel::get($id);
		$rights = $data['rights'];
		$arr    = explode(',', $rights);                       //将字符串以,分割为一个一维数组
		$count  = count($arr);
//		dump($arr);		
		$lists = MenusModel::getMenuList();                   //获取Menus表中的菜单列表
//		dump($lists);
		$MenuList = $this->CateSameMenu($lists);
//		dump($MenuList);
		$newCheckedMenus = array();       
		foreach ($MenuList as  $value)                                                                     //根据数据库中权限给菜单添加是否选中属性
		{
			for($t=0;$t<$count;$t++)                                                                       //查找选中的一级菜单
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

			if(count($value['son'][0])>0)                                                                  //查找选中的二级菜单（有子菜单）
			{
				for($k=0;$k<count($value['son']);$k++)                                                     //查找当前一级菜单下的二级菜单个数
	            {
	            	for($t=0;$t<$count;$t++)
					{
						if($value['son'][$k]['id'] == (int)$arr[$t])                                        //二级菜单被选中
						{
							$value['son'][$k]['checked'] = 1;
							if(!empty($value['son'][$k]['allow']))                                          //有权限菜单
							{
								for($y=0;$y<count($value['son'][$k]['allow']);$y++)                         //判断哪些权限被选中，计算权限菜单长度
								{
									for($n=0;$n<$count;$n++)
									{
										if($value['son'][$k]['allow'][$y]['id'] == (int)$arr[$n])
										{
											$value['son'][$k]['allow'][$y]['checked'] = 1;
											break;
										}
										else
										{
											$value['son'][$k]['allow'][$y]['checked'] = 0;
										}
									}
								}
							}
							break;
						}
						else                                                                                //二级菜单没选中，查看权限菜单是否选中
						{
							$value['son'][$k]['checked'] = 0;
							if(!empty($value['son'][$k]['allow']))                                          //有权限菜单
							{
								for($y=0;$y<count($value['son'][$k]['allow']);$y++)                         //判断哪些权限被选中，计算权限菜单长度
								{
									for($n=0;$n<$count;$n++)
									{
										if($value['son'][$k]['allow'][$y]['id'] == (int)$arr[$n])
											$value['son'][$k]['allow'][$y]['checked'] = 1;
										else
											$value['son'][$k]['allow'][$y]['checked'] = 0;
									}
								}
							}
						}
					}
	            }
			}
			$newCheckedMenus[] = $value;
		}
//		dump($newCheckedMenus);
		$this->assign('id',$data['rid']);
		$this->assign('user',$data['title']);
		$this->assign('list',$newCheckedMenus);

		return $this->fetch('admin_role_edit');
	}

	public function update(Request $request)
	{
		if($request->isPost())
		{
			$data  = $request->param();
			$count = RolesModel::get($data['roleId']);
			$map   = [
				'title'  => $data['roleName'],
				'rights' => $data["father_checkboxs"].','.$data["son_checkboxs"].','.$data["allow_checkboxs"],
			];
			$result = RolesModel::update($map,['rid'=>$data['roleId']]);
			if(!is_null($result))
			{
				$this->params['code']  =200;
	            $this->params['msg']   ='更新成功';
	            $this->params['data']  ='';
			}
			else
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='更新错误';
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