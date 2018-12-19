<?php
namespace app\admin\controller;

use think\Request;
use think\Session;
use think\Paginator;
use app\admin\controller\Base;
use app\admin\model\Banner as BannerModel;
use app\admin\model\Picture as PictureModel;
use app\admin\model\LabelCate as LabelCateModel;

class Banner extends base
{
	protected $params;

	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
    public function index()
	{
    	//从数据库的banner表中获取所有数据
		$list   = BannerModel::order("id DESC")->paginate(4);
		//计算数据表中数据条数
		$count = BannerModel::count();
		// 获取分页显示
		$page  = $list->render();
		//将数据反馈给前台
		$this->assign('Blist',$list);
		$this->assign('count',$count);
		$this->assign('page', $page);
		//渲染模板
    	return $this->fetch('banner_list');
    }

    public function create()
    {
    	$type    = LabelCateModel::all(['pid'=>0]);
    	$sontype = LabelCateModel::all(['level'=>2]);
    	
    	$this->assign('type',$type);
		$this->assign('tempsontype',json_encode($sontype));

    	return $this->fetch('banner_add');
    }

    public function add()
	{
		if($this->request->isPost())
		{
			$curUser = Session::get('user_info.name');
			//获取提交的数据
			$data    = $this->request->param(true);
			//获取表单的上传图片
			$file    = $this->request->file('image');
			//判断文件是否上传成功
			if(empty($file))
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
					$typeName = $data['tag'];                                //获取图片所属前台标签名字
					$imgname=$file->getInfo()['name'];                       //获取上传的图片名称
					$uploadImageName = PictureModel::all(['name'=>$imgname]);//获取上传图片记录表picture中所有满足条件的记录
					if(empty($uploadImageName))                              //图片从未上传到服务器中
					{
						$result = $file->move(ROOT_PATH.'public/static/index/picture/'.$typeName,$imgname);
						if($result)
						{
							$picture_path = '/static/index/picture/'.$typeName;
							$picture_name = $imgname;
							//将图片路径、图片名和使用次数写入表中，使用次数为1，表示本次使用。
							$res = PictureModel::create(['path'=>$picture_path,'name'=>$picture_name,'use_count'=>1]);
							$imageid = PictureModel::max('id');
						}
					}
					else                                                     //picture表中有同名的图片
					{
						$picture_path   = '/static/index/picture/'.$typeName;
						$picture_name   = $imgname;
						$data_same_flag = 0;                                 //表中无相同记录
						foreach ($uploadImageName as $key => $value) 
						{
							if(($value['path'] == $picture_path)&&($value['name'] == $imgname))//picture中有相同数据
							{
								$useCount = $value['use_count']+1;
								$data_same_flag = 1;
								$res = PictureModel::update(['use_count'=>$useCount],['name'=>$picture_name]);
								$imageid = $value['id'];
								break;
							}
							else                                                              //picture中无相同记录
							{
								$data_same_flag = 0;
							}
						}
						if($data_same_flag == 0)                                              //picture中无相同记录
						{
							$result = $file->move(ROOT_PATH.'public/static/index/picture/'.$typeName,$imgname);//有同名的图片则覆盖
							if($result)
							{
								$res = PictureModel::create(['path'=>$picture_path,'name'=>$picture_name,'use_count'=>1]);
								$imageid = PictureModel::max('id');
							}
						}
					}
					if($res)                                                                  //在picture表中上传或更新成功
					{
						//设置字段名称
						$data['image']      = $imgname;
						$order              = BannerModel::where('tag',$typeName)->count();
						$data['order']      = $order+1;
						$data['start_time'] = strtotime($data['start_time']);
						$data['end_time']   = strtotime($data['end_time']);	
						$data['imageid']    = $imageid;
						//将数据写入数据库
						$result = BannerModel::create($data);
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
			$result = BannerModel::get($id);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				if($result->getData('status') == 1)
				{
					BannerModel::update(['status'=>0],['id'=>$id]);
				}
				else
				{
					BannerModel::update(['status'=>1],['id'=>$id]);
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
  			$result = BannerModel::get($id);
  			$temppicture =PictureModel::get($result['imageid']);
  			if($temppicture['use_count']>1)
  			{
  				PictureModel::update(['use_count'=>($temppicture['use_count']-1)],['id'=>$temppicture['id']]);
  			}
  			else
  			{
  				PictureModel::destroy($temppicture['id']);
  			}

  			if($result['is_delete']== 0)
  			{
  				$this->params['code']  =400;
	            $this->params['msg']   ='不能删除该图片';
	            $this->params['data']  ='';
  			}
  			else
  			{
  				BannerModel::destroy($id,true);
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

	public function reset()
	{
		$datas = BannerModel::onlyTrashed()->find();    //仅仅需要查询软删除的数据
		if(is_null($datas))	
		{
			$this->params['code']  =200;
            $this->params['msg']   ='无数据需要恢复';
            $this->params['data']  ='';
		}
		else
		{
			$result = BannerModel::update(['delete_time'=>NULL],['is_delete'=>1]);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='恢复错误';
	            $this->params['data']  ='';
			}
			else
			{
				$this->params['code']  =200;
	            $this->params['msg']   ='恢复成功';
	            $this->params['data']  ='';
			}
		}
		return json($this->params);
	}

	public function edit(Request $request)
	{
		$id     = $request->param('id');
		$type = LabelCateModel::all(['pid'=>0]);
    	$this->assign('type',$type);
		$data   = BannerModel::get($id);
		if(is_null($data))
		{
			$this->params['code']  =400;
            $this->params['msg']   ='数据查询失败';
            $this->params['data']  ='';
            return json($this->params);
		}
		else
		{
			$this->assign('list',$data);
			$this->assign('type',$type);
			return $this->fetch('banner_edit');
		}
	}

	public function save(Request $request)
	{
		if($request->isPost())
		{
			$curUser = Session::get('user_info.name');
			//获取提交的数据
			$data = array_filter($request->param(true));
			$oldorder = $data['tag'];

			//获取表单的上传图片
			$file = $this->request->file('image');
			//判断文件是否上传成功
			if(!empty($file))                                     //更新上传图片
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
					//处理本条记录
					$tempdata = BannerModel::get($data['id']);            //获取当前需要编辑的数据
					$temppicture = PictureModel::get($tempdata['imageid']);     
					if($temppicture['use_count'] >= 1)                     //该条数据被引用多次
					{
						PictureModel::update(['use_count'=>($temppicture['use_count']-1)],['id'=>$temppicture['id']]);
					}
					$temppicture1 = PictureModel::get($temppicture['id']);
					if($temppicture1['use_count'] = 0)                                                  //该条数据只被引用一次
					{
						PictureModel::destroy(['id'=>$temppicture1['id']]);
					}
					//处理其他条数据
					$uploadImageName = PictureModel::all(['path'=>$picture_path,'name'=>$imgname]);
					$picture_same    = 0;                                 //有相同记录
					if(empty($uploadImageName))                           //图片名不同，无相同记录
					{
						$result = $file->move(ROOT_PATH.'public/static/index/picture/'.$typeName,$imgname);
						PictureModel::create(['path'=>$picture_path,'name'=>$imgname,'use_count'=>1]);
						$imageid = PictureModel::max('id');
					}
					else                                                  //图片相同
					{
						foreach ($uploadImageName as $key => $value) 
						{
							if(($value['path'] == $picture_path)&&($value['name'] == $imgname))
							{
								PictureModel::update(['use_count'=>($value['use_count']+1)],['id'=>$value['id']]);
								$imageid = $value['id'];
								$picture_same = 0;
								break;
							}
							else
							{
								$picture_same = 1;
							}
						}
					}
					if($picture_same == 1)
					{
						PictureModel::create(['path'=>$picture_path,'name'=>$imgname,'use_count'=>1]);
						$imageid = PictureModel::max('id');
					}
					//设置字段名称
					$data['image']      = $imgname;
					$data['imageid']    = $imageid;
					$data['start_time'] = strtotime($data['start_time']);
					$data['end_time']   = strtotime($data['end_time']);	
					$result = BannerModel::update($data,['id'=>$data['id']]);
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
				$data['start_time'] = strtotime($data['start_time']);
				$data['end_time']   = strtotime($data['end_time']);	
				//将数据写入数据库
				$result = BannerModel::update($data,['id'=>$data['id']]);
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
}