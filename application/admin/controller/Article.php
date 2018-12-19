<?php
namespace app\admin\controller;

use think\File;
use think\Request;
use think\Session;
use think\Paginator;
use app\admin\controller\Base;
use app\admin\model\Picture as PictureModel;
use app\admin\model\Article as ArticleModel;
use app\admin\model\LabelCate as LabelCateModel;

class Article extends Base
{
	protected $params;

	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}

	public function index(Request $request)
	{
		global $cacheRedis;                                           //引用全局变量                                 
        $redis   = $cacheRedis->handler();
        $cur_day = $this->getdayNumber(time());
		$controllName = $request->controller();
		$redis->incr('access'.$controllName.$cur_day);                //该控制器的今天的访问次数

		$datas = ArticleModel::order("id DESC")->paginate(8);
		$page  = $datas->render();
		$count = ArticleModel::count();

		$this->assign('list',$datas);
		$this->assign('count',$count);
		$this->assign('page', $page);

		return $this->fetch('article_list');
	}

	public function add()
	{
		$type    = LabelCateModel::all(['pid'=>0]);
		$sontype = LabelCateModel::all(['level'=>2]);
		$this->assign('type',$type);
		$this->assign('tempsontype',json_encode($sontype));
		return $this->fetch('article_add');
	}

	public function upload()
	{
        $file = $this->request->file('file');//file是传文件的名称，webloader插件固定写入的。因为webloader插件会写入一个隐藏input
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
    }

    public function save()
    {
    	if($this->request->isPost())
		{
			$data    = $this->request->param(true);
			$file    = $this->request->file('image');
			//判断文件是否上传成功
			if(empty($file) || empty($data))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ="文件为空";
	            $this->params['data']  ='';
			}
			else
			{
				//设置验证条件
				$rule=[
					'ext'  => 'jpg,png,gif,bmp,ico',
					'size' => '3000000',
				];
				//验证图片信息
				$result = $file->validate($rule);
				if(!$result)
				{
					$this->params['code']  =400;
		            $this->params['msg']   ="文件格式错误";
		            $this->params['data']  ='';
				}
				else
				{
					//-----------------上传图片-------------------------------------------------//
					$typeName = $data['catename'];
					$imgname=$file->getInfo()['name'];
					$picture_path = '/static/index/picture/'.$typeName;
					$uploadImageName = PictureModel::get(['name'=>$imgname]);
					if(empty($uploadImageName))                       //图片从未上传到服务器中
					{
						$result = $file->move(ROOT_PATH.'public/static/index/picture/'.$typeName,$imgname);
						if($result)
						{
							$picture_path = '/static/index/picture/'.$typeName;
							$picture_name = $imgname;
							$res = PictureModel::create(['path'=>$picture_path,'name'=>$picture_name]);
						}
					}
					else
					{
						$picture_path = '/static/index/picture/'.$typeName;
						$picture_name = $imgname;
						if($uploadImageName['path']!=$picture_path)
						{
							$result = $file->move(ROOT_PATH.'public/static/index/picture/'.$typeName,$imgname);
							if($result)
							{
								$res = PictureModel::create(['path'=>$picture_path,'name'=>$picture_name]);
							}
						}
					}
					//-----------------------------上传数据----------------------------------------------//
					if(empty($data['status']))
						$data['status'] = 0;
					else
						$data['status'] = 1;
					$where['cateid']       = $data['cateid'];
					$where['catename']     = $data['catename'];
					$where['type']         = $data['type'];
					$data['image']         = $imgname;
					$order                 = ArticleModel::where($where)->count();
					$data['order']         = $order+1;
					$result                = ArticleModel::create($data);
					if(is_null($result))
					{
						$this->params['code']  =400;
			            $this->params['msg']   ="数据写入失败";
			            $this->params['data']  ='';
					}
					else
					{
						$this->params['code']  =200;
			            $this->params['msg']   ="数据写入成功";
			            $this->params['data']  ='';
					}	
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

    public function detail(Request $request)
    {
    	$id   = $request->param('id');
    	$data = ArticleModel::get(['id'=>$id]);
    	if(is_null($data))
    	{
    		$this->params['code']  =400;
            $this->params['msg']   ="读取数据失败";
            $this->params['data']  ='';
    	}
    	else
    	{
    		$this->assign('data',$data);
    		return $this->fetch('article_detail');
    	}
    }

    public function edit(Request $request)
    {
    	$id      = $request->param('id');
    	$data    = ArticleModel::get(['id'=>$id]);
    	$type    = LabelCateModel::all(['pid'=>0]);
		$sontype = LabelCateModel::all(['level'=>2]);

		$this->assign('data',$data);
		$this->assign('type',$type);
		$this->assign('sontype',$sontype);

    	return $this->fetch('article_edit');
    }

    public function update(Request $request)
	{
		if($request->isPost())
		{
			$curUser = Session::get('user_info.name');
			//获取提交的数据
			$data = array_filter($request->param(true));
			$oldorder = $data['cateid'];

			//获取表单的上传图片
			$file = $this->request->file('image');
			//判断文件是否上传成功
			if(!empty($file))
			{
				//设置验证条件
				$rule=[
					'ext'  => 'jpg,png',
					'size' => '3000000',
				];
				//验证图片信息
				$result = $file->validate($rule);
				if(!$result)
				{
					$this->params['code']  =400;
		            $this->params['msg']   ="图片格式错误";
		            $this->params['data']  ='';
				}
				else
				{
					$typeName     = $data['tag'];                         //获取图片所属前台标签名字
					$picture_path = '/static/index/picture/'.$typeName;
					$imgname=$file->getInfo()['name'];

					$uploadImageName = PictureModel::all(['path'=>$picture_path,'name'=>$imgname]);

					if(empty($uploadImageName))
					{
						$result = $file->move(ROOT_PATH.'public/static/index/picture/'.$typeName,$imgname);
					}
	
					//设置字段名称
					$data['image']      = $imgname;
					$result = ArticleModel::update($data,['id'=>$data['id']]);
					PictureModel::create(['path'=>$picture_path,'name'=>$imgname]);
					if(is_null($result))
					{
						$this->params['code']  =400;
			            $this->params['msg']   ="数据写入失败";
			            $this->params['data']  ='';
					}
					else
					{
						$this->params['code']  =200;
			            $this->params['msg']   ="数据写入成功";
			            $this->params['data']  ='';
					}
				}
			}
			else
			{
				//将数据写入数据库
				$result = ArticleModel::update($data,['id'=>$data['id']]);
				if(is_null($result))
				{
					$this->params['code']  =400;
		            $this->params['msg']   ="数据写入失败";
		            $this->params['data']  ='';
				}
				else
				{
					$this->params['code']  =200;
		            $this->params['msg']   ="数据写入成功";
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


    public function setStatus(Request $request)
	{
		if($request->isGet())
		{
			$id     = $request->param('id');
			$result = ArticleModel::get($id);
			if(empty($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				if($result->getData('status') == 1)
				{
					ArticleModel::update(['status'=>0],['id'=>$id]);
				}
				else
				{
					ArticleModel::update(['status'=>1],['id'=>$id]);
				}

				$this->params['code']  =200;
	            $this->params['msg']   ='修改成功';
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

    public function delete(Request $request)
	{
		if($request->isGet())
		{
  			$id = $request->param('id');
  			$result = ArticleModel::get($id);

  			if($result['is_delete']== 0)
  			{
  				$this->params['code']  =400;
	            $this->params['msg']   ='不能删除该文章';
	            $this->params['data']  ='';
  			}
  			else
  			{
  				ArticleModel::destroy($id,true);   //真实删除
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

}