<?php
namespace app\index\controller;

use think\Cache;
use think\View;
use think\Session;
use think\Request;
use think\Collection;
use think\Controller;
use app\index\controller\CommonIndex;

class Manage extends CommonIndex
{
	
	public function index(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$view = new View;
		return $view->fetch('home@login/index');
	}
}
