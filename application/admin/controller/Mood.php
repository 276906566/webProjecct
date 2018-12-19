<?php
namespace app\admin\controller;

use think\Config;
use think\Request;
use app\admin\controller\Base;
use app\admin\model\Mood as MoodModel;

class Mood extends Base
{
	protected $params;
	
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}

	public function index()
	{
		$data = Base::get_config('mood_config');
		foreach ($data as $k=>$v) 
		{
			if (empty($v['NAME'])) 
				unset($data[$k]);
		}
		$this->assign('list',$data);
	
		return $this->fetch('mood_setting');
	}

	public function edit(Request $request)
	{
		$id = $request->param();
		$data = Base::get_config('mood_config');
		foreach ($data as $k=>$v) 
		{
			if($v['ID']!=$id['id']) 
			{
				unset($data[$k]);
			}
		}
		$this->assign('data',$data['MOOD'.$id['id']]);
		return $this->fetch('mood_edit');
	}

	public function update(Request $request)
	{
		if($request->isPost())
		{
			//获取提交的数据
			$data = array_filter($request->param(true));
			$temp ='MOOD'.$data['ID'];
			//获取表单的上传图片
			$file = $this->request->file('image');
			//判断文件是否上传成功
			if(!empty($file))                                     //更新上传图片
			{
				//设置验证条件
				$rule=[
					'ext'  => 'jpg,png,gif,ico,bmp',
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
					$picture_path = '/static/index/picture/mood/';
					$imgname=$file->getInfo()['name'];
					$result = $file->move(ROOT_PATH.'public/static/index/picture/mood/',$imgname);
					if(is_null($result))
					{
						$this->params['code']  =400;
			            $this->params['msg']   ="图片上传失败";
			            $this->params['data']  ='';
					}
					else
					{
						$setting = config('mood_config');
						foreach ($setting as $key => $value) 
						{
							if($key == $temp)
							{
								$setting[$key]['USE']  = trim($data['USE']);
								$setting[$key]['NAME'] = trim($data['NAME']);
								$setting[$key]['PIC']  = $picture_path.$imgname;
							}
						}
						Base::update_config($setting,'mood_config');	

						$this->params['code']  =200;
			            $this->params['msg']   ="数据写入成功";
			            $this->params['data']  ='';
					}
				}
			}
			else                                                  //不更新图片
			{
				$setting = config('mood_config');
				foreach ($setting as $key => $value) 
				{
					if($key == $temp)
					{
						if($data['USE'] == 0)
							$setting[$key]['USE']  = 0;
						else
							$setting[$key]['USE']  = 1;
					}
				}
				Base::update_config($setting,'mood_config');
				$this->params['code']  =200;
	            $this->params['msg']   ="数据写入成功";
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

	public function pre_show()
	{
		$where['cateid']    = 0;
		$where['contentid'] = 0;
		$data = MoodModel::get($where);    //预览时，栏目id=0，文章id=0

		$setting = config('mood_config');
		foreach ($setting as $key=>$value) //给setting数据添加2个字段：fields和per
		{
			$setting[$key]['FIELDS'] = 'n'.$setting[$key]['ID'];
			if (!isset($data[$setting[$key]['FIELDS']])) 
				$data[$setting[$key]['FIELDS']] = 0;
			if (isset($data['total']) && !empty($data['total'])) 
			{
				$setting[$key]['PER'] = ceil((intval($data[$setting[$key]['FIELDS']])/intval($data['total'])) * 60);
			} else {
				$setting[$key]['PER'] = 0;
			}
		}

		foreach ($setting as $k=>$v)   //取出未用的的数据项
		{
			if($v['USE']!=1) 
			{
				unset($setting[$k]);
			}
		}

		$this->assign('setting',$setting);
		$this->assign('data',$data);
		return $this->fetch('mood_show');
/*		ob_start();                      //打开输出控制缓冲

		$html = ob_get_contents();       //返回输出缓冲区的内容
		ob_clean();                      //清空（擦掉）输出缓冲区
*/
	}

	public function poster(Request $request)
	{
		$id = $request->param('id');
		if (!in_array($id, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)))
		{
			$this->params['code']  =400;
            $this->params['msg']   ="请求错误";
            $this->params['data']  ='';
		}
		else
		{
			$fields = 'n'.$id;
			$where['cateid']    = 0;
			$where['contentid'] = 0;
			$datas = MoodModel::get($where);
			if($datas)
			{
				MoodModel::update(['total'=>$datas['total']+1,$fields=>$datas[$fields]+1],['id'=>$datas['id']]);
			}
			else
			{
				$where1['total']     = 1;
				$where1[$fields]     = 1;
				$where1['cateid']    = 0;
				$where1['contentid'] = 0;
				dump($where1);
				MoodModel::create($where1);
			}
//			$data = MoodModel::get($where);
//			dump($data);
			$setting = config('mood_config');
			foreach ($setting as $key=>$value) //给setting数据添加2个字段：fields和per
			{
				$setting[$key]['FIELDS'] = 'n'.$setting[$key]['ID'];
				if (!isset($data[$setting[$key]['FIELDS']])) 
					$data[$setting[$key]['FIELDS']] = 0;
				if (isset($data['total']) && !empty($data['total'])) 
				{
					$setting[$key]['PER'] = ceil((intval($data[$setting[$key]['FIELDS']])/intval($data['total'])) * 60);
				} else {
					$setting[$key]['PER'] = 0;
				}
			}

			foreach ($setting as $k=>$v)   //取出未用的的数据项
			{
				if($v['USE']!=1) 
				{
					unset($setting[$k]);
				}
			}
			$this->assign('setting',$setting);
			$this->assign('data',$data);
			return $this->fetch('mood_show');
		}
	}
}