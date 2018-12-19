<?php
namespace app\index\controller;

use think\Session;
use think\Collection;
use think\Controller;
use app\admin\model\Banner as BannerModel;
use app\admin\model\Article as ArticleModel;

class Index extends CommonIndex
{
    public function index()
    {
//------------------最新产品轮播---------------------//
    	$where['tag']    = 'Index';
    	$where['type']   = 1;
		$where['status'] = 1;
		$pictures = BannerModel::all($where);
//------------------最新新闻展示---------------------//
        $where1['catename']  = 'News';
        $where1['recommend'] = 1;
        $where1['status']    = 1;
        $newList =ArticleModel::where($where1)->order("id DESC")->limit(4)->select();
//------------------公司介绍-------------------------//
        $where2['catename']  = 'Company';
        $where2['recommend'] = 1;
        $where2['status']    = 1;
        $companyArticle  = ArticleModel::where($where2)->select();
        if(!$companyArticle)
        {
            $this->assign('companyArticle',null);
        }
        else
        {
            foreach ($companyArticle as $key => $value) 
            {
                $article = $value;
            }
            $this->assign('companyArticle',$article);
        }
		$this->assign('pictureShow',$pictures);
		$this->assign('newList',$newList);
        
        return $this->fetch('index');
    }

    

    

}
