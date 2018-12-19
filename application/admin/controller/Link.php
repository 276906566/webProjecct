<?php
namespace app\admin\controller;

use think\Request;
use think\Paginator;
use app\admin\controller\Base;
use app\admin\model\Link as LinkModel;
use app\admin\model\Picture as PictureModel;

class Link extends Base
{
	protected $params;
	
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function index()
	{
		$datas = LinkModel::order("id DESC")->paginate(8);
		$page  = $datas->render();
		$count = LinkModel::count();

		$this->assign('count',$count);
		$this->assign('list',$datas);
		$this->assign('page',$page);

		return $this->fetch('link_list');
	}

	public function add()
	{
		return $this->fetch('link_add');
	}

	public function save(Request $request)
	{
		if($this->request->isPost())
		{
			$data    = $this->request->param(true);
			$file    = $this->request->file('image');

			//判断文件是否上传成功
			if(empty($file) && ($data['linktype']==0))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ="文件为空";
	            $this->params['data']  ='';
			}
			else
			{
				if(!empty($file) && ($data['linktype']==0))               //上传logo链接
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
						$typeName = 'link';
						$imgname=$file->getInfo()['name'];
						$picture_path = '/static/index/picture/'.$typeName;
						$uploadImageName = PictureModel::all(['name'=>$imgname]);
						if(empty($uploadImageName))                       //图片从未上传到服务器中
						{
							$result = $file->move(ROOT_PATH.'public/static/index/picture/'.$typeName,$imgname);
							if($result)
							{
								$picture_path = '/static/index/picture/'.$typeName;
								$picture_name = $imgname;
								$res = PictureModel::create(['path'=>$picture_path,'name'=>$picture_name,'use_count'=>1]);
							$imageid = PictureModel::max('id');
							}
						}
						else                                            //有相同的图片名但路径不同
						{
							$picture_path = '/static/index/picture/'.$typeName;
							$picture_name = $imgname;
							$data_same_flag = 0; 
							foreach ($uploadImageName as $key => $value) 
							{
								if(($value['path']==$picture_path)&&($value['name']==$imgname))
								{
									$useCount = $value['use_count']+1;
									$data_same_flag = 1;
									$res = PictureModel::update(['use_count'=>$useCount],['name'=>$picture_name]);
									$imageid = $value['id'];
									break;
								}
								else
								{
									$data_same_flag = 0;
								}
							}
							if($data_same_flag == 0) 
							{
								$result = $file->move(ROOT_PATH.'public/static/index/picture/'.$typeName,$imgname);
								if($result)
								{
									$res = PictureModel::create(['path'=>$picture_path,'name'=>$picture_name,'use_count'=>1]);
									$imageid = PictureModel::max('id');
								}
							}
							
						}
						$data['imageid'] = $imageid;
						//-----------------------------上传数据----------------------------------------------//
						$data['logo']         = '/static/index/picture/'.$typeName.'/'.$imgname;
						foreach( $data as $k=>$v) 
						{
						 	if($k == 'image') 
						 		unset($data[$k]);
						}
						$result                = LinkModel::create($data);
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
				else                            //上传文字链接
				{
					foreach( $data as $k=>$v) 
					{
					 	if($k == 'image') 
					 		unset($data[$k]);
					}
					$result = LinkModel::create($data);
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

	public function setPass(Request $request)
	{
		if($this->request->isGet())
		{
			$data = $request->param();
			$result = LinkModel::get($data['id']);
			if(empty($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{

				LinkModel::update(['pass'=>$data['pass']],['id'=>$data['id']]);

				$this->params['code']  =200;
	            $this->params['msg']   ='修改成功';
	            $this->params['data']  ='';
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

	public function edit(Request $request)
	{
		$id = $request->param();
		$result = LinkModel::get($id);
		if($result)
		{
			$this->assign('data',$result);
			return $this->fetch('link_edit');
		}
		else
		{
			$this->params['code']  =400;
            $this->params['msg']   ="查询出错";
            $this->params['data']  ='';
            return json($this->params);
		}
	}

	public function update(Request $request)
	{
		$data    = $this->request->param(true);
		$file    = $this->request->file('image');

		//判断文件是否上传成功
		if(empty($file) && ($data['linktype']==0))
		{
			$this->params['code']  =400;
            $this->params['msg']   ="文件为空";
            $this->params['data']  ='';
		}
		else
		{
			if(!empty($file) && ($data['linktype']==0))               //上传logo链接
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
					$typeName = 'link';
					$imgname=$file->getInfo()['name'];
					$picture_path = '/static/index/picture/'.$typeName;
					//处理本条记录
					$tempdata = LinkModel::get($data['id']);            //获取当前需要编辑的数据
					$temppicture = PictureModel::get($tempdata['imageid']);     
					if($temppicture['use_count'] > 1)                     //该条数据被引用多次
					{
						PictureModel::update(['use_count'=>($temppicture['use_count']-1)],['id'=>$temppicture['id']]);
					}
					else                                                 //该条数据只被引用一次
					{
						PictureModel::destroy(['id'=>$temppicture['id']]);
					}

					//处理其他条数据
					$uploadImageName = PictureModel::all(['path'=>$picture_path,'name'=>$imgname]);
					$picture_same    = 0;   
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
					//-----------------------------上传数据----------------------------------------------//
					$data['imageid']    = $imageid;
					$data['logo']         = '/static/index/picture/'.$typeName.'/'.$imgname;
					foreach( $data as $k=>$v) 
					{
					 	if($k == 'image') 
					 		unset($data[$k]);
					}
					$result                = LinkModel::update($data,['id'=>$data['id']]);
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
			if(empty($file) && ($data['linktype']==1))                            //上传文字链接
			{
				foreach( $data as $k=>$v) 
				{
				 	if($k == 'image') 
				 		unset($data[$k]);
				}
				$result = LinkModel::update($data,['id'=>$data['id']]);
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
		return json($this->params);
	}

	public function delete(Request $request)
	{
		if($this->request->isGet())
		{
			$id = $request->param('id');
  			$result = LinkModel::get($id);
  			$temppicture =PictureModel::get($result['imageid']);
  			if($temppicture['use_count']>1)
  			{
  				PictureModel::update(['use_count'=>($temppicture['use_count']-1)],['id'=>$temppicture['id']]);
  			}
  			else
  			{
  				PictureModel::destroy($temppicture['id']);
  			}

			$result = LinkModel::destroy($id);
			if(empty($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='删除失败';
	            $this->params['data']  ='';
			}
			else
			{
				$this->params['code']  =200;
	            $this->params['msg']   ='删除成功';
	            $this->params['data']  ='';
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