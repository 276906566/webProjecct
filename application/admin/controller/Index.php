<?php
namespace app\admin\controller;

use think\Cache;
use think\Config;
use think\Session;
use think\Request;
use think\Collection;
use think\cache\driver\Redis;
use app\admin\controller\Base;
use app\admin\model\Users as UsersModel;
use app\admin\model\Sites as SitesModel;
use app\admin\model\Menus as MenusModel;
use app\admin\model\Roles as RolesModel;
use app\admin\model\Permission as PermissionModel;
use app\admin\model\LabelCate as LabelCateModel;


class Index extends base
{
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function index()
	{
		global $cacheRedis;                                           //引用全局变量                                 
        $redis   = $cacheRedis->handler();

		$userRole = Session::get('user_role');
//---------------设置后台菜单------------------------------------------------//
		if(!Cache::get('user_menu'.$userRole))                              //设置菜单项
		{
			$this->showAllRolesMenuList();
			$curMenuList = Cache::get('user_menu'.$userRole);
			Session::set('user_menu',$curMenuList);
		}
		else
		{
			$curMenuList = Cache::get('user_menu'.$userRole);
			Session::set('user_menu',$curMenuList);
		}

		if(!Session::get('menu_permission'))                                 //设置显示区权限
		{
			$role          = Session::get('user_id');
			$allPermission = UsersModel::get(['id'=>$role])->Roles->rights;
			$arr           = explode(',', $allPermission);                   //将字符串以,分割为一个一维数组
			$count         = count($arr);
			$lists         = MenusModel::getMenuList();                      //获取Menus表中的菜单列表
			$newCheckedMenus1 = array();       
			foreach ($lists as  $key => $value)                              //根据数据库中权限给菜单添加是否选中属性
			{
				for($t=0;$t<$count;$t++)                                      //查找选中的一级菜单
				{
					if($value['Mmid'] == (int)$arr[$t])
					{
						$value['checked'] = 1;
						break;
					}
					else
					{
						$value['checked'] = 0;
					}
				}
				$newCheckedMenus1[] = $value;
			}
			$SessionList = $this->CateSessionMenu($newCheckedMenus1);
			Session::set('menu_permission',$SessionList);
		}	
		
//--------------设置前台菜单------------------------------------------//
		if(!Cache::get('Index_nav'))
		{
			$list = $this->navigation();
    		Cache::set('Index_nav',$list);
		}

		//今天访问统计
		$cur_day = $this->getdayNumber(time());
		$this->assign('article',$redis->get('accessArticle'.$cur_day)?$redis->get('accessArticle'.$cur_day):0);
		$this->assign('picture',$redis->get('accessPicture'.$cur_day)?$redis->get('accessPicture'.$cur_day):0);
		$this->assign('comment',$redis->get('accessComment'.$cur_day)?$redis->get('accessComment'.$cur_day):0);
		$this->assign('productcate',$redis->get('accessProductCate'.$cur_day)?$redis->get('accessProductCate'.$cur_day):0);
		$this->assign('users',$redis->get('accessUsers'.$cur_day)?$redis->get('accessUsers'.$cur_day):0);
		//昨天访问统计
		$yes_day = $this->getdayNumber(time()-24*60*60);
		$this->assign('yarticle',$redis->get('accessArticle'.$yes_day)?$redis->get('accessArticle'.$yes_day):0);
		$this->assign('ypicture',$redis->get('accessPicture'.$yes_day)?$redis->get('accessPicture'.$yes_day):0);
		$this->assign('ycomment',$redis->get('accessComment'.$yes_day)?$redis->get('accessComment'.$yes_day):0);
		$this->assign('yproductcate',$redis->get('accessProductCate'.$yes_day)?$redis->get('accessProductCate'.$yes_day):0);
		$this->assign('yusers',$redis->get('accessUsers'.$yes_day)?$redis->get('accessUsers'.$yes_day):0);
		//本周访问统计
		$wsumarticle = $wsumpicture=$wsumcomment=$wsumproductcate=$wsumusers=0;
		$cur_week  = date("w",time());
		$cur_day   = date("j",time());
		if($cur_week == 0)
		{
			$cur_start = $cur_day-6;
		}
		else
		{
			$cur_start = $cur_day-$cur_week+1;
		}
		for($i=$cur_start;$i<=date("j",time());$i++)
		{
			$wsumarticle     += $redis->get('accessArticle'.$i);
			$wsumpicture     += $redis->get('accessPicture'.$i);
			$wsumcomment     += $redis->get('accessComment'.$i);
			$wsumproductcate += $redis->get('accessProductCate'.$i);
			$wsumusers       += $redis->get('accessUsers'.$i);
		}
		$this->assign('warticle',$wsumarticle);
		$this->assign('wpicture',$wsumpicture);
		$this->assign('wcomment',$wsumcomment);
		$this->assign('wproductcate',$wsumproductcate);
		$this->assign('wusers',$wsumusers);
		//本月访问统计
		$csumarticle = $csumpicture=$csumcomment=$csumproductcate=$csumusers=0;
		$cur_month  = date("n",time());
		for($i=1;$i<=date("j",time());$i++)
		{
			$csumarticle     += $redis->get('accessArticle'.$i);
			$csumpicture     += $redis->get('accessPicture'.$i);
			$csumcomment     += $redis->get('accessComment'.$i);
			$csumproductcate += $redis->get('accessProductCate'.$i);
			$csumusers       += $redis->get('accessUsers'.$i);
		}
		$redis->set('MaccessArticle'.$cur_month,$csumarticle);               //本月的访问次数，用于统计本年度访问次数用
		$redis->set('MaccessPicture'.$cur_month,$csumpicture);               //本月的访问次数，用于统计本年度访问次数用
		$redis->set('MaccessComment'.$cur_month,$csumcomment);				 //本月的访问次数，用于统计本年度访问次数用
		$redis->set('MaccessProductCate'.$cur_month,$csumproductcate);		 //本月的访问次数，用于统计本年度访问次数用
		$redis->set('MaccessUsers'.$cur_month,$csumusers);					 //本月的访问次数，用于统计本年度访问次数用

		$this->assign('marticle',$csumarticle);
		$this->assign('mpicture',$csumpicture);
		$this->assign('mcomment',$csumcomment);
		$this->assign('mproductcate',$csumproductcate);
		$this->assign('musers',$csumusers);
		//本年度访问次数
		$Ysumarticle = $Ysumpicture=$Ysumcomment=$Ysumproductcate=$Ysumusers=0;
		$cur_month  = date("n",time());
		for($i=1;$i<=$cur_month;$i++)
		{
			$Ysumarticle     += $redis->get('MaccessArticle'.$i);
			$Ysumpicture     += $redis->get('MaccessPicture'.$i);
			$Ysumcomment     += $redis->get('MaccessComment'.$i);
			$Ysumproductcate += $redis->get('MaccessProductCate'.$i);
			$Ysumusers       += $redis->get('MaccessUsers'.$i);
		}
		$this->assign('Yarticle',$Ysumarticle);
		$this->assign('Ypicture',$Ysumpicture);
		$this->assign('Ycomment',$Ysumcomment);
		$this->assign('Yproductcate',$Ysumproductcate);
		$this->assign('Yusers',$Ysumusers);

		$BeginDate = date('Y-m-01', strtotime(date("Y-m-d")));              //今年第一天
		$EndDate   = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));//今年最后一天
		$endTime   = strtotime($EndDate.'23:59:59');
		if(time()>$endTime)
		{
			for($i=1;$i<=31;$i++)                                          //清空每天的访问记录
			{
				$redis->set('accessArticle'.$i,0);
				$redis->set('accessPicture'.$i,0);
				$redis->set('accessComment'.$i,0);
				$redis->set('accessProductCate'.$i,0);
				$redis->set('accessUsers'.$i,0);
			}
			for($i=1;$i<=12;$i++)                                          //清空每月的访问记录
			{
				$redis->set('MaccessArticle'.$i,0);
				$redis->set('MaccessPicture'.$i,0);
				$redis->set('MaccessComment'.$i,0);
				$redis->set('MaccessProductCate'.$i,0);
				$redis->set('MaccessUsers'.$i,0);
			}
			$cur_year  = date("Y",time());                                //保存每年的访问记录
			$redis->set('YaccessArticle'.$cur_year,$Ysumarticle);
			$redis->set('YaccessPicture'.$cur_year,$Ysumpicture);
			$redis->set('YaccessComment'.$cur_year,$Ysumcomment0);
			$redis->set('YaccessProductCate'.$cur_year,$Ysumproductcate);
			$redis->set('YaccessUsers'.$cur_year,$Ysumusers);
		}
		
		return $this->fetch('index');
	}

	public function welcome()
	{
		global $cacheRedis;                                           //引用全局变量
        $redis    = $cacheRedis->handler();
		return $this->fetch('welcome');
	}

	//根据当前用户的角色形成菜单选项，菜单数据放在session中的user_menu变量中，用于动态显示菜单
	public function showMenu()
	{
		$list     = MenusModel::getMenuDetailList();
		$MenuList = $this->CateTwoLevelMenuDetail($list);
		$userRole = Session::get('user_role');
		$data     = RolesModel::get(['role'=>$userRole]);
		$rights   = $data['rights'];
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
//		dump($newCheckedMenus);
		Session::set('user_menu',$newCheckedMenus);
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

	//将同一层级的菜单项目放在一个数组内，便于前台显示
	public function CateSessionMenu($arr)
	{
		$controller = 0;
		$TempOmenu  = array();
		$ResultMenu = array();

		foreach ($arr as $key => $value) 
		{
			if($value['Mlevel'] == 2)
			{
				if(!empty($controller))
				{
					$ResultMenu[$controller['controller']] = $TempOmenu;
				}
				array_splice($TempOmenu,0, count($TempOmenu));
				$controller = MenusModel::get(['mid'=>$value['Mmid']]);
			}
			if($value['Mlevel'] == 3)
			{
				$data = PermissionModel::get(['name'=>$value['Mtitle']]);
				if($value['checked'] == 1) 
					$TempOmenu[$data['id']]   = 1;
				else
					$TempOmenu[$data['id']]   = 0;
			}
		}

		if(!empty($controller))
		{
			$ResultMenu[$controller['controller']] = $TempOmenu;
		}

		return Collection::make($ResultMenu)->toArray(); 
	}

	public function navigation()
    {
    	$datas = LabelCateModel::getCateOrder();
    	$templist  = $this->CateThreeLevelIndexMenuDetail($datas);

    	return Collection::make($templist)->toArray(); 
    }

	//仅获取三级菜单，也就是说本系统仅支持三级菜单
	public function CateThreeLevelIndexMenuDetail($arr)
	{
		$k=0;
		$Fmenu=array();                          //一级菜单
		$Omenu=array();                          //二级级菜单
		$Gmenu=array();                          //三级级菜单
		$TempOmenu=array();
		$TempGmenu=array();
		$ResultMenu=array();
		$tempMenu = array();

		foreach ($arr as $key => $value) 
		{
			if($value['level'] == '一级菜单')
			{
				if(!empty($Fmenu))
				{
					$tempMenu['father']  = $Fmenu;
					$tempMenu['son']     = $TempOmenu;
					$tempMenu['grandson']= $TempGmenu;
					$ResultMenu[$k]      = $tempMenu;
					$k++;
				}
				array_splice($tempMenu, 0, count($tempMenu));
				array_splice($Fmenu,    0, count($Fmenu));
				array_splice($Omenu,    0, count($Omenu));
				array_splice($Gmenu,    0, count($Gmenu));
				array_splice($TempOmenu,0, count($TempOmenu));
				array_splice($TempGmenu,0, count($TempGmenu));
				$Fmenu['id']         = $value['mid'];
				$Fmenu['name']       = $value['title'];
				$Fmenu['controller'] = $value['controller'];
				$Fmenu['hidden']     = $value['ishidden'];
				$Fmenu['status']     = $value['status'];
			}
			else 
			{
				if ($value['level'] == '二级菜单')
				{
					if(!empty($TempGmenu))
					{
						$tempMenu['father']  = $Fmenu;
						$tempMenu['son']     = $TempOmenu;
						$tempMenu['grandson']= $TempGmenu;
						$ResultMenu[$k]      = $tempMenu;
						$k++;
						array_splice($tempMenu, 0, count($tempMenu));
						array_splice($Omenu,    0, count($Omenu));
						array_splice($Gmenu,    0, count($Gmenu));
						array_splice($TempOmenu,0, count($TempOmenu));
						array_splice($TempGmenu,0, count($TempGmenu));
					}
					if((empty($TempGmenu))&&(!empty($TempOmenu)))
					{
						$tempMenu['father']  = $Fmenu;
						$tempMenu['son']     = $TempOmenu;
						$tempMenu['grandson']= $TempGmenu;
						$ResultMenu[$k]      = $tempMenu;
						$k++;
						array_splice($tempMenu, 0, count($tempMenu));
						array_splice($Omenu,    0, count($Omenu));
						array_splice($Gmenu,    0, count($Gmenu));
						array_splice($TempOmenu,0, count($TempOmenu));
						array_splice($TempGmenu,0, count($TempGmenu));
					}
					$Omenu['id']         = $value['mid'];
					$Omenu['name']       = $value['title'];
					$Omenu['controller'] = $value['controller'];
					$Omenu['method']     = $value['method'];
					$Omenu['hidden']     = $value['ishidden'];
					$Omenu['status']     = $value['status'];
					$TempOmenu           = $Omenu;
				}
				else                                             //三级菜单
				{
					if ($value['level'] == '三级菜单')
					{
						$Gmenu['id']         = $value['mid'];
						$Gmenu['name']       = $value['title'];
						$Gmenu['controller'] = $value['controller'];
						$Gmenu['method']     = $value['method'];
						$Gmenu['hidden']     = $value['ishidden'];
						$Gmenu['status']     = $value['status'];
						$TempGmenu[]         = $Gmenu;
					}
				}
			}	
		}

		if(!empty($Fmenu))
		{
			$tempMenu['father'] = $Fmenu;
			$tempMenu['son']    = $TempOmenu;
			$tempMenu['grandson']= $TempGmenu;
			$ResultMenu[$k] = $tempMenu;
		}
		return Collection::make($ResultMenu)->toArray(); 
	}

	public function setconfig($pat, $rep)
	{
	    /**
	     * 原理就是 打开config配置文件 然后使用正则查找替换 然后在保存文件.
	     * 传递的参数为2个数组 前面的为配置 后面的为数值.  正则的匹配为单引号  如果你的是分号 请自行修改为分号
	     * $pat[0] = 参数前缀;  例:   default_return_type
	       $rep[0] = 要替换的内容;    例:  json
	     */
	    if (is_array($pat) and is_array($rep)) {
	        for ($i = 0; $i < count($pat); $i++) {
	            $pats[$i] = '/\'' . $pat[$i] . '\'(.*?),/';
	            $reps[$i] = "'". $pat[$i]. "'". "=>" . "'".$rep[$i] ."',";
	        }
	        $fileurl = APP_PATH . "config.php";
	        $string = file_get_contents($fileurl); //加载配置文件
	        $string = preg_replace($pats, $reps, $string); // 正则查找然后替换
	        file_put_contents($fileurl, $string); // 写入配置文件
	        return true;
	    } else {
	        return flase;
	    }
	}
}