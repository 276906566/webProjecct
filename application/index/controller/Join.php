<?php
namespace app\index\controller;

use think\Session;
use think\Request;
use app\index\controller\CommonIndex;
use app\admin\model\Banner as BannerModel;
use app\admin\model\Article as ArticleModel;

class Join extends CommonIndex
{
	public function index(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['cateid'] = 'Join';
		$where['type']   = 1;
		$where['status'] = 1;
		
		$joinData  = ArticleModel::where($where)->select();
		if(!$joinData)
		{
			$this->assign('joinData',null);
		}
		else
		{
			foreach ($joinData as $key => $value) 
			{
				$article = $value['content'];
			}
			$this->assign('joinData',$article);
		}

		return $this->fetch('join/index');
	}

	public function condition(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['cateid'] = 'Join';
		$where['type']   = 2;
		$where['status'] = 1;
		
		$joinCondition  = ArticleModel::where($where)->select();
		if(!$joinCondition)
		{
			$this->assign('joinCondition',null);
		}
		else
		{
			foreach ($joinCondition as $key => $value) 
			{
				$article = $value['content'];
			}
			$this->assign('joinCondition',$article);
		}

		return $this->fetch('join/condition');
	}
/*
	public function method(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['cateid'] = 'Join';
		$where['type']   = 3;
		$where['status'] = 1;
		
		$joinMethod  = ArticleModel::where($where)->select();
		if(!$joinMethod)
		{
			$this->assign('joinMethod',null);
		}
		else
		{
			foreach ($joinMethod as $key => $value) 
			{
				$article = $value['content'];
			}
			$this->assign('joinMethod',$article);
		}

		return $this->fetch('join/method');
	}
*/
	public function application(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		
		return $this->fetch('join/application');
	}

	public function shop(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['tag']    = 'Join';
		$where['type']   = 4;
		$where['status'] = 1;
		$pictures = BannerModel::where($where)->paginate(8);
		$page     = $pictures->render();

		$this->assign('pictures',$pictures);
		$this->assign('page',$page);
		
		return $this->fetch('join/shop');
	}

}