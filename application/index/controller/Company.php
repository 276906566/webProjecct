<?php
namespace app\index\controller;

use think\Session;
use think\Request;
use app\index\controller\CommonIndex;
use app\admin\model\Banner as BannerModel;
use app\admin\model\Article as ArticleModel;
use app\admin\model\LabelCate as LabelCateModel;

class Company extends CommonIndex
{
	public function index(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['catename'] = 'Company';
		$where['type']   = 3;
		$where['status'] = 1;
		$companyArticle  = ArticleModel::where($where)->select();

		if(empty($companyArticle))
		{
			$this->assign('companyArticle',null);
		}
		else
		{
			foreach ($companyArticle as $key => $value) 
			{
				$article = $value['content'];
			}
			$this->assign('companyArticle',$article);
		}
		return $this->fetch('index');
	}

	public function culture(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['catename'] = 'Company';
		$where['type']     = 4;
		$where['status']   = 1;
		$companyCulture  = ArticleModel::where($where)->select();

		if(empty($companyCulture))
		{
			$this->assign('companyCulture',null);
		}
		else
		{
			foreach ($companyCulture as $key => $value) 
			{
				$article = $value['content'];
			}
			$this->assign('companyCulture',$article);
		}
		return $this->fetch('culture');
	}

	public function honor(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['tag']    = 'Company';
		$where['type']   = 5;
		$where['status'] = 1;
		$pictures = BannerModel::where($where)->paginate(8);
		$page     = $pictures->render();

		$this->assign('pictures',$pictures);
		$this->assign('page',$page);
		return $this->fetch('honor');
	}

	public function ethic(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		$where['catename'] = 'Company';
		$where['type']   = 6;
		$where['status'] = 1;
		$companyEthic = ArticleModel::where($where)->select();

		if(empty($companyEthic))
		{
			$this->assign('companyEthic',null);
		}
		else
		{
			foreach ($companyEthic as $key => $value) 
			{
				$article = $value['content'];
			}
			$this->assign('companyEthic',$article);
		}
		
		return $this->fetch('ethic');
	}

	public function detail(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);      //设置左侧菜单
		$this->assign('LocationMenuList',null);   //设置location
		$id   = $request->param('id');
		$data1 = BannerModel::get(['id'=>$id]);
		
		if(empty($data1))
		{
			$this->assign('data',null);
		}
		else
		{
			$this->assign('data',$data1);
		}
		return $this->fetch('company/honorinfo');
	}
}
