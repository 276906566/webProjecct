<?php
namespace app\index\controller;

use think\Session;
use think\Request;
use think\Collection;
use app\index\controller\CommonIndex;
//use app\admin\model\Banner as BannerModel;
use app\admin\model\Article as ArticleModel;
use app\admin\model\LabelCate as LabelCateModel;
class News extends CommonIndex
{
	public function index(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['catename'] = 'News';
		$where['type']     = 8;
		$where['status']   = 1;
		
		$companyNew  = ArticleModel::where($where)->paginate(10);
		$page        = $companyNew->render();
		if(!$companyNew)
		{
			$this->assign('companyNew',null);
			$this->assign('page',null);
		}
		else
		{
			$this->assign('companyNew',$companyNew);
			$this->assign('page',$page);
		}
		return $this->fetch('news/index');
	}

	public function industry(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['catename'] = 'News';
		$where['type']     = 9;
		$where['status']   = 1;
		
		$industryNew  = ArticleModel::where($where)->paginate(10);
		$page        = $industryNew->render();
		if(!$industryNew)
		{
			$this->assign('industryNew',null);
			$this->assign('page',null);
		}
		else
		{
			$this->assign('industryNew',$industryNew);
			$this->assign('page',$page);
		}
		return $this->fetch('news/industry');
	}

	public function detail(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['catename'] = 'News';
		$where['status']   = 1;

		$id   = $request->param('id');
		$where['id']     = $id;
		$data = ArticleModel::where($where)->select();
		if(!$data)
		{
			$this->assign('data',null);
		}
		else
		{
			foreach ($data as $key => $value) 
			{
				$article = $value;
			}
			$this->assign('data',$article);
		}
		return $this->fetch('news/detail');	
	}


	
}