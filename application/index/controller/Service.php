<?php
namespace app\index\controller;

use think\Session;
use think\Request;
use think\captcha\Captcha;
use app\index\controller\CommonIndex;
use app\admin\model\Article as ArticleModel;
use app\admin\model\Comment as CommentModel;
class Service extends CommonIndex
{
	protected $params;
	public function index(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		//获取网页内容数据
		$where['catename'] = 'Service';
		$where['type']     = 17;
		$where['status']   = 1;
		$serviceIndex  = ArticleModel::where($where)->select();
		//分发网页数据
		if(empty($serviceIndex))
		{
			$this->assign('data',null);
		}
		else
		{
			foreach ($serviceIndex as $key => $value) 
			{
				$data = $value['content'];
			}
			$this->assign('data',$data);
		}
		return $this->fetch('service/index');
	}

	public function contact(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		//获取网页内容数据
		$where['catename'] = 'Service';                            //控制器名
		$where['type']     = 18;                                    //数据表中排序号
		$where['status']   = 1;                                    //上架或下架
		$serviceIndex  = ArticleModel::where($where)->select();
		//分发网页数据
		if(empty($serviceIndex))
		{
			$this->assign('contactData',null);
		}
		else
		{
			foreach ($serviceIndex as $key => $value) 
			{
				$data = $value['content'];
			}
			$this->assign('contactData',$data);
		}
		return $this->fetch('service/contact');
	}

	public function feedback(Request $request)
	{
		$id = $request->param('id');
		$this->getIndexNavLeftMenuList($id);    //设置左侧菜单
		$this->getIndexLocationMenuList($id);   //设置location
		//获取网页内容数据
		$where['catename'] = 'Service';                            //控制器名
		$where['type']     = 19;                                    //数据表中排序号
		$where['status']   = 1;                                    //上架或下架
		$serviceIndex  = ArticleModel::where($where)->select();
		//分发网页数据
		if(empty($serviceIndex))
		{
			$this->assign('feedbackData',null);
		}
		else
		{
			foreach ($serviceIndex as $key => $value) 
			{
				$data = $value['content'];
			}
			$this->assign('feedbackData',$data);
		}
		return $this->fetch('service/feedback');
	}

	public function comment(Request $request)
	{
		if($this->request->isPost())
		{
			$data = $request->param();
			$this->getIndexNavLeftMenuList($data['id']);    //设置左侧菜单
			$this->getIndexLocationMenuList($id);   //设置location
			$captcha = new Captcha();
			$result  = $captcha->check($data['ImgCode']);     //验证码校验
			if($result ===false)
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='验证码错误';
	            $this->params['data']  ='';
			}
			else
			{
				$map = [
					'title'   => $data['title'],
					'content' => $data['content'],
					'company' => $data['company'],
					'name'    => $data['name'],
					'tel'     => $data['tel'],
					'qq'      => $data['qq'],
					'email'   => $data['email'],
					'status'  => 0
				];

				$result = CommentModel::create($map);
				if($result)
				{
					$this->params['code']  =200;
		            $this->params['msg']   ="添加成功";
		            $this->params['data']  ='';
				}
				else
				{
					$this->params['code']  =400;
		            $this->params['msg']   ="添加失败";
		            $this->params['data']  ='';
				}
			}
		}
		else
		{
			$this->params['code']  =400;
            $this->params['msg']   ="请求出错";
            $this->params['data']  ='';
		}

		return json($this->params);   
	}
}