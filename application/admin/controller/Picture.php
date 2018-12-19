<?php
namespace app\admin\controller;

use think\Request;
use think\Session;
use app\admin\controller\Base;
use app\admin\model\Picture as PictureModel;

class Picture extends base
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

		$data  = PictureModel::order("id DESC")->paginate(12);;
		$count = PictureModel::count();
		$page  = $data->render();

		$this->assign('count',$count);
		$this->assign('list',$data);
		$this->assign('page',$page);
		return $this->fetch('picture_show');
	}

	public function delete(Request $request)
	{     
		if($request->isPost())
		{
		    $ids     = $request->param('checked_lists');  
		    if(strlen($ids) > 1)
		    {
		    	$tmpArr  = explode(',', $ids); 
				$count   = PictureModel::max('id'); 

				for($i=1;$i<=$count;$i++)
				{
					if(in_array($i, $tmpArr))
					{
						$data   = PictureModel::get($i);
						$path   = ROOT_PATH.'/public'.$data['path'].'/'.$data['name'];//定义文件存放的路径
						$unlink = PictureModel::unlink($path);
						if($unlink)
						{
							PictureModel::destroy(['id'=>$i]);
							$this->params['code']  =200;
				            $this->params['msg']   ='删除成功';
				            $this->params['data']  ='';
						}
						else
						{
							$this->params['code']  =400;
				            $this->params['msg']   ='删除失败';
				            $this->params['data']  ='';
						}
					}
				}
		    }
		    else
		    {
		    	$data   = PictureModel::get($ids);
				$path   = ROOT_PATH.'/public'.$data['path'].'/'.$data['name'];//定义文件存放的路径
				$unlink = PictureModel::unlink($path);
				if($unlink)
				{
					PictureModel::destroy(['id'=>$ids]);
					$this->params['code']  =200;
		            $this->params['msg']   ='删除成功';
		            $this->params['data']  ='';
				}
				else
				{
					$this->params['code']  =400;
		            $this->params['msg']   ='删除失败';
		            $this->params['data']  ='';
				}
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