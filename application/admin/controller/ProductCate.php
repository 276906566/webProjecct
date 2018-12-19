<?php
namespace app\admin\controller;

use think\Request;
use think\Paginator;
use think\Collection;
use app\admin\controller\Base;
use app\admin\model\ProductCate as ProductCateModel;

class ProductCate extends Base
{
	protected $params;
	
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function cate(Request $request)
	{
		global $cacheRedis;                                           //引用全局变量                                 
        $redis   = $cacheRedis->handler();
        $cur_day = $this->getdayNumber(time());
		$controllName = $request->controller();
		$redis->incr('access'.$controllName.$cur_day);                //该控制器的今天的访问次数

		$list  = ProductCateModel::getCate();
		$count = ProductCateModel::count();

		$this->assign('cate',$list);
		$this->assign('count',$count);
		return $this->fetch('product_category');
	}

	public function cateAdd()
	{
		$list   = ProductCateModel::getCate();
		$this->assign('cate',$list);
		return $this->fetch('product_category_add');
	}

	public function cateSave(Request $request)
	{
		if($request->isPost())
		{
			$data = $request->param();
			if($data['catePid'] == '-1')             //用户需要添加顶级菜单项
			{
				$order =ProductCateModel::withTrashed()->where('pid',0)->count();
				$map   =[
					'pid'        => 0,
					'order'      => $order+1,
					'name'       => $data['cateName'],
				];
                $result    = ProductCateModel::create($map);
                if(is_null($result))
                {
                	$this->params['code']  =400;
		            $this->params['msg']   ='新增失败';
		            $this->params['data']  ='';
                }
                else
                {
                	$this->params['code']  =200;
		            $this->params['msg']   ='新增成功';
		            $this->params['data']  ='';
                }
			}
			else
			{
				$tempdata = ProductCateModel::get($data['catePid']);
				$temppid  = $tempdata['mid'];   
				$order    = ProductCateModel::withTrashed()->where('pid',$temppid)->count(); 
				$map      = [
					'pid'        => $temppid,
					'order'      => $order+1,
					'name'       => $data['cateName'],
				];
				$result    = ProductCateModel::create($map);
                if(is_null($result))
                {
                	$this->params['code']  =400;
		            $this->params['msg']   ='新增失败';
		            $this->params['data']  ='';
                }
                else
                {
                	$this->params['code']  =200;
		            $this->params['msg']   ='新增成功';
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

	public function cateFind(Request $request)
	{
		if($request->isGet())
		{
			$data =ProductCateModel::withTrashed()->field('mid,pid,name')->select();
			if(is_null($data))
            {
            	$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
            }
            else
            {
            	$this->params['code']  =200;
	            $this->params['msg']   ='查询成功';
	            $this->params['data']  =$data;
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

	public function setStatus(Request $request)
	{
		if($request->isGet())
		{
			$id     = $request->param('id');
			$result = ProductCateModel::get($id);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				$mids = ProductCateModel::findItem($result['mid']);                 //需要锁定/解锁的数据项mid集合
				if($result->getData('status') == 1)
				{
					ProductCateModel::update(['status'=>0],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						ProductCateModel::update(['status'=>0],['mid'=>$mids[$i]]);
					}
				}
				else
				{
					ProductCateModel::update(['status'=>1],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						ProductCateModel::update(['status'=>1],['mid'=>$mids[$i]]);
					}
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

	public function setIshidden(Request $request)
	{
		if($request->isGet())
		{
			$id     = $request->param('id');
			$result = ProductCateModel::get($id);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				$mids = ProductCateModel::findItem($result['mid']);                 //需要锁定/解锁的数据项mid集合
				if($result->getData('ishidden') == 1)
				{
					ProductCateModel::update(['ishidden'=>0],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						ProductCateModel::update(['ishidden'=>0],['mid'=>$mids[$i]]);
					}
				}
				else
				{
					ProductCateModel::update(['ishidden'=>1],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						ProductCateModel::update(['ishidden'=>1],['mid'=>$mids[$i]]);
					}
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
  			$result = ProductCateModel::get($id);
  			if($result['is_delete'] == 0)
  			{
  				$this->params['code']  =400;
	            $this->params['msg']   ='不能删除该菜单项';
	            $this->params['data']  ='';
  			}
  			else
  			{
  				ProductCateModel::destroy($id);                                     //删除父菜单项
  				$mids = ProductCateModel::findItem($result['mid']);                 //需要删除的数据项mid集合
  				for($i=0;$i<count($mids);$i++)                                //删除子菜单项
				{
					ProductCateModel::destroy($mids[$i]);
				}
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
		$datas = ProductCateModel::onlyTrashed()->find();    //仅仅需要查询软删除的数据
		if(is_null($datas))	
		{
			$this->params['code']  =200;
            $this->params['msg']   ='无数据需要恢复';
            $this->params['data']  ='';
		}
		else
		{
			$result = ProductCateModel::update(['delete_time'=>NULL],['is_delete'=>1]);
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
		$mid    = $request->param('id');
		$data   = ProductCateModel::get($mid);                  //取出当前节点的数据
		if($data['pid'] == 0)
		{
			$fdata = '顶级菜单项';
		}
		else
		{
			$fdatas  = ProductCateModel::get($data['pid']);     //取出父节点的数据
			$fdata   = $fdatas['name'];
		}
		

		$this->assign('ftitle',$fdata);
		$this->assign('list',$data);

		return $this->fetch('product_category_edit');
	}

	public function update(Request $request)
	{
		if($request->isPost())
		{   
			$tempdata         = $request->except('cateFid');
			$id               = $tempdata['cateMid'];
			$title            = $tempdata['cateName'];
			if($title =="")
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='提交数据不完整';
	            $this->params['data']  ='';
			}
			else
			{
				$map    = ['name'=>$tempdata['cateName'],'controller'=>$tempdata['cateController'],'method'=>$tempdata['cateMethod'],'ishidden'=>$tempdata['cateIshidden'],'status'=>$tempdata['cateStatus']];
				$result = ProductCateModel::update($map,['mid'=>$id]);
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