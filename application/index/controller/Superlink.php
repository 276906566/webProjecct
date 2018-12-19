<?php
namespace app\index\controller;

use think\Session;
use think\Request;
use app\index\controller\CommonIndex;
use app\admin\model\Link as LinkModel;

class Superlink extends CommonIndex
{
	public function index(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['linktype'] = '0';
		$where['pass'] = 2;
		$linkData  = LinkModel::where($where)->select();
		$where1['linktype'] = '1';
		$where1['pass'] = 2;
		$linkData1  = LinkModel::where($where1)->select();
		if(!$linkData)
		{
			$this->assign('linkData',null);
		}
		else
		{
			$this->assign('linkData',$linkData);
		}
		if(!$linkData1)
		{
			$this->assign('linkData1',null);
		}
		else
		{
			$this->assign('linkData1',$linkData1);
		}

		return $this->fetch('index');
	}
}