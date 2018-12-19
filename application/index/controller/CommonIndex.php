<?php
namespace app\index\controller;

use think\Cache;
use think\Session;
use think\Collection;
use think\Controller;
use app\admin\model\LabelCate as LabelCateModel;

class CommonIndex extends Controller
{
	public function __construct()
	{
		parent::__construct();
		$list = $this->getIndexNavTopMenuList();     //显示top导航菜单
		$this->assign('navTopList',$list);
	}

	//从前台3级菜单中选择第一级和第二级菜单，来显示navtop
	public function getIndexNavTopMenuList()
	{
		$i       = 0;
		$y       = 0;
		$curItemarray = array();
		//获取顶层导航栏数据
		$list = Cache::get('Index_nav');
//		dump($list);
		//获取当前位置和侧边栏数据
		foreach ($list as $key => $value)                  
		{
			if($key >=1)
			{
				if($curItem['father']['controller'] == $value['father']['controller'])
				{
					$y = $y+1;
					if(!empty($value['son']))
					{
						$curItemarray[$i]['son'][$y] = $value['son'];
					}
					else
					{
						$curItemarray[$i]['son'][$y] = null;
					}
				}
				else
				{
					$curItem = $value;
					$y = 0;
					$i = $i+1;
					$curItemarray[$i]['father']         = $value['father'];
					if(!empty($value['son']))
					{
						$curItemarray[$i]['son'][$y]    = $value['son'];
					}
					else
					{
						$curItemarray[$i]['son']        = null;
					}		
				}
			}
			else
			{
				$curItem = $value;
				$curItemarray[0]['father']     = $value['father'];
				if(!empty($value['son']))
				{
					$curItemarray[0]['son'][$y]    = $value['son'];
				}
				else
				{
					$curItemarray[0]['son']    = null;
					$i = $i+1;
				}
			}			
		}
		return Collection::make($curItemarray)->toArray(); 
	}

	//根据点击的id寻找该ID的二级和三级菜单
	public function getIndexNavLeftMenuList($cateLabelid)
	{
		if(is_numeric($cateLabelid))
		{
			$data = LabelCateModel::get($cateLabelid);
			if($data['level'] == '一级菜单')
			{
				$tempNavLeftMenuList = LabelCateModel::getCateOrder((int)$cateLabelid);
			}
			else
			{
				if($data['level'] == '二级菜单')
				{
					$pid = $data['pid'];
					$tempNavLeftMenuList = LabelCateModel::getCateOrder((int)$pid);
				}
				else                            //该菜单为三级菜单
				{
					$mid = $data['pid'];
					$data = LabelCateModel::get($mid);
					$pid = $data['pid'];
					$tempNavLeftMenuList = LabelCateModel::getCateOrder((int)$pid);
				}
			}
		} 
		$navLeftMenuList = $this->getIndexNavLeftTwoMenuList($tempNavLeftMenuList);
		$this->assign('navLeftMenuList',$navLeftMenuList);
	}

	public function getIndexNavLeftTwoMenuList($arr)
	{
		$i       = 0;
		$y       = 0;
		$curItemarray = array();
		//获取当前位置和侧边栏数据
		foreach ($arr as $key => $value)                  
		{
			if($value['level'] == '二级菜单')
			{
				if($key != 0)
					$i = $i+1;
				$curItemarray[$i]['father'] = $value;
			}
			else
			{
				$curItemarray[$i]['son'][$y] = $value;
				$y = $y+1;
			}
		}
		return Collection::make($curItemarray)->toArray(); 
	}

	public function getIndexLocationMenuList($cateLabelid)
	{
		$navLocationMenuList = $this->getIndexLocationTempMenuList($cateLabelid);
		$this->assign('LocationMenuList',$navLocationMenuList);
	}

	public function getIndexLocationTempMenuList($cateLabelid)
	{
		if(is_numeric($cateLabelid))
		{
			$data = LabelCateModel::get($cateLabelid);
			if($data['level'] == '一级菜单')
			{
				$tempLocationMenuList[] = $data['title'];
			}
			else
			{
				if($data['level'] == '二级菜单')
				{
					$pid = $data['pid'];
					$data1 = LabelCateModel::get((int)$pid);
					$tempLocationMenuList[] = $data1['title'];
					$tempLocationMenuList[] = $data['title'];
				}
				else                            //该菜单为三级菜单
				{
					$mid = $data['pid'];
					$data1 = LabelCateModel::get($mid);
					$pid = $data['pid'];
					$data2 = LabelCateModel::getCateOrder((int)$pid);
					$tempLocationMenuList[] = $data2['title'];
					$tempLocationMenuList[] = $data1['title'];
					$tempLocationMenuList[] = $data['title'];
				}
			}
		} 
		return Collection::make($tempLocationMenuList)->toArray(); 
	}

}

