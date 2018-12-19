<?php
namespace app\admin\controller;

use think\Request;
use think\Session;
use think\Paginator;
use app\admin\controller\Base;
use app\admin\model\Comment as CommentModel;
use app\admin\model\Feedback as FeedbackModel;
use app\admin\model\LabelCate as LabelCateModel;

class Comment extends Base
{
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

		$data  = CommentModel::where('status','<=',1)->order("id DESC")->paginate(4);
		$page  = $data->render();
		$count = CommentModel::where('status','<=',1)->count();

		$this->assign('count',$count);
		$this->assign('list',$data);
		$this->assign('page',$page);
		return $this->fetch('comment_list');
	}

	public function delete(Request $request)
	{
		if($request->isGet())
		{
  			$id = $request->param('id');
  			$result = CommentModel::get($id);

  			if($result['is_delete']== 0)
  			{
  				$this->params['code']  =400;
	            $this->params['msg']   ='不能删除该意见';
	            $this->params['data']  ='';
  			}
  			else
  			{
  				CommentModel::destroy($id);
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
		$datas = CommentModel::onlyTrashed()->find();    //仅仅需要查询软删除的数据
		if(is_null($datas))	
		{
			$this->params['code']  =200;
            $this->params['msg']   ='无数据需要恢复';
            $this->params['data']  ='';
		}
		else
		{
			$result = CommentModel::update(['delete_time'=>NULL],['is_delete'=>1]);
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

	public function show(Request $request)
	{
		$id     = $request->param('id');
		$result = CommentModel::get($id);
		if(empty($result))
		{
			$this->params['code']  =400;
            $this->params['msg']   ='数据查询失败';
            $this->params['data']  ='';
            return json($this->params);
		}
		else
		{
			if($result->getData('status') == 0)
			{
				CommentModel::update(['status'=>1],['id'=>$id]);
			}
			$this->assign('info',$result);
			return $this->fetch('comment/comment_show');
		}
	}

	public function setStatus(Request $request)
	{
		if($request->isGet())
		{
			$id     = $request->param('id');
			$result = CommentModel::get($id);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				if($result->getData('status') != 2)
				{
					CommentModel::update(['status'=>2],['id'=>$id]);
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

	public function edit(Request $request)
	{
		$id     = $request->param('id');
		$result = CommentModel::get($id);
		if(!empty($result))
		{
			$map = [
				'id'      => $result['id'],
				'title'   => $result['title'],
				'content' => $result['content'],
			];

			$datalist = FeedbackModel::all(['fid'=>$id]);
			if(empty($datalist))
			{
				$this->assign('sdata',$map);
				$this->assign('datalist',null);
			}
			else
			{
				$this->assign('sdata',$map);
				$this->assign('datalist',$datalist);
			}
			return $this->fetch('comment_feedback');
		}
	}

	public function save(Request $request)
	{
		if($request->isPost())
		{   
			$tempdata         = $request->param();
			$id               = $tempdata['fid'];
			$title            = $tempdata['content'];
			$username         = Session::get('user_info.name');
			if($title =="")
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='提交数据不完整';
	            $this->params['data']  ='';
			}
			else
			{
				$map    = ['message'=>$tempdata['content'],'fid'=>$id,'user'=>$username];
				$result = FeedbackModel::create($map);
				if(!is_null($result))
				{
					$this->params['code']  =200;
		            $this->params['msg']   ='数据更新成功';
		            $this->params['data']  ='';
				}
				else
				{
					$this->params['code']  =400;
		            $this->params['msg']   ='数据更新失败';
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