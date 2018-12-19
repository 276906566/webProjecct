<?php
namespace app\admin\controller;

use think\Cache;
use think\Request;
use think\Paginator;
use think\Collection;
use app\admin\controller\Base;
use app\admin\model\LabelCate as LabelCateModel;

class LabelCate extends Base
{
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function index()
	{
		$list  = LabelCateModel::getCate();
		$count = LabelCateModel::count();

		$this->assign('cate',$list);
		$this->assign('count',$count);
		return $this->fetch('label_category');
	}

	public function labelCateAdd()
	{
		$list   = LabelCateModel::getCate();
		$this->assign('cate',$list);
		$this->assign('levels',json_encode($list));
		return $this->fetch('label_category_add');
	}

	public function labelCateSave(Request $request)
	{
		if($request->isPost())
		{
			$data = $request->param();
			if($data['catePid'] == '-1')             //用户需要添加顶级菜单项
			{
				$order =LabelCateModel::withTrashed()->where('pid',0)->count();
				$map   =[
					'pid'        => 0,
					'order'      => $order+1,
					'title'      => $data['cateName'],
					'level'      => 1,
					'pc'         => $data['pc'],
					'controller' => $data['cateController'],
					'status'     => $data['cateStatus'],
					'ishidden'   => $data['cateIshidden'],
				];
                $result    = LabelCateModel::create($map);
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
				$tempdata = LabelCateModel::get($data['catePid']);
				$temppid  = $tempdata['mid'];   
				$order    = LabelCateModel::withTrashed()->where('pid',$temppid)->count(); 
				$levels   = LabelCateModel::getMidAndLevel();
				foreach ($levels as $key => $value) 
				{
					if($value['Mmid'] == $temppid )
					{
						$tempLevel = $value['Mlevel']+1;
						break;
					}
				}
				$map      = [
					'pid'        => $temppid,
					'order'      => $order+1,
					'title'      => $data['cateName'],
					'pc'         => $data['pc'],
					'level'      => $tempLevel,
					'controller' => $data['cateController'],
					'method'     => $data['cateMethod'],
					'status'     => $data['cateStatus'],
					'ishidden'   => $data['cateIshidden'],
				];
				$result    = LabelCateModel::create($map);
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

	public function labelCateFind(Request $request)
	{
		if($request->isGet())
		{
			$data =LabelCateModel::withTrashed()->field('mid,pid,title')->select();
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
			$result = LabelCateModel::get($id);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				$mids = LabelCateModel::findItem($result['mid']);                 //需要锁定/解锁的数据项mid集合
				if($result->getData('status') == 1)
				{
					LabelCateModel::update(['status'=>0],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						LabelCateModel::update(['status'=>0],['mid'=>$mids[$i]]);
					}
				}
				else
				{
					LabelCateModel::update(['status'=>1],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						LabelCateModel::update(['status'=>1],['mid'=>$mids[$i]]);
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
			$result = LabelCateModel::get($id);
			if(is_null($result))
			{
				$this->params['code']  =400;
	            $this->params['msg']   ='查询失败';
	            $this->params['data']  ='';
			}
			else
			{
				$mids = LabelCateModel::findItem($result['mid']);                 //需要锁定/解锁的数据项mid集合
				if($result->getData('ishidden') == 1)
				{
					LabelCateModel::update(['ishidden'=>0],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						LabelCateModel::update(['ishidden'=>0],['mid'=>$mids[$i]]);
					}
				}
				else
				{
					LabelCateModel::update(['ishidden'=>1],['mid'=>$id]);
					for($i=0;$i<count($mids);$i++)
					{
						LabelCateModel::update(['ishidden'=>1],['mid'=>$mids[$i]]);
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
  			$result = LabelCateModel::get($id);
  			if($result['is_delete'] == 0)
  			{
  				$this->params['code']  =400;
	            $this->params['msg']   ='不能删除该菜单项';
	            $this->params['data']  ='';
  			}
  			else
  			{
  				LabelCateModel::destroy($id);                                     //删除父菜单项
  				$mids = LabelCateModel::findItem($result['mid']);                 //需要删除的数据项mid集合
  				for($i=0;$i<count($mids);$i++)                                //删除子菜单项
				{
					LabelCateModel::destroy($mids[$i]);
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
		$datas = LabelCateModel::onlyTrashed()->find();    //仅仅需要查询软删除的数据
		if(is_null($datas))	
		{
			$this->params['code']  =200;
            $this->params['msg']   ='无数据需要恢复';
            $this->params['data']  ='';
		}
		else
		{
			$result = LabelCateModel::update(['delete_time'=>NULL],['is_delete'=>1]);
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

	public function labelCateEdit(Request $request)
	{
		$mid    = $request->param('id');
		$data   = LabelCateModel::get($mid);                  //取出当前节点的数据
		if($data['pid'] == 0)
		{
			$fdata = '顶级菜单项';
		}
		else
		{
			$fdatas  = LabelCateModel::get($data['pid']);     //取出父节点的数据
			$fdata   = $fdatas['title'];
		}
		

		$this->assign('ftitle',$fdata);
		$this->assign('list',$data);

		return $this->fetch('label_category_edit');
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
				$map    = ['title'=>$tempdata['cateName'],'controller'=>$tempdata['cateController'],'method'=>$tempdata['cateMethod'],'ishidden'=>$tempdata['cateIshidden'],'status'=>$tempdata['cateStatus'],'pc'=>$tempdata['pc']];
				$result = LabelCateModel::update($map,['mid'=>$id]);
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

	public function resetcache()
	{
		if(Cache::get('Index_nav'))
		{
			$list = $this->navigation();
    		Cache::set('Index_nav',$list);
    		$this->params['code']  =200;
            $this->params['msg']   ='缓存已修改';
            $this->params['data']  ='';
		}
		else
		{
			$this->params['code']  =400;
            $this->params['msg']   ='缓存未修改';
            $this->params['data']  ='';
		}
		return json($this->params);
	}

	public function navigation()
    {
    	$datas = LabelCateModel::getCateOrder();
    	$templist  = $this->CateThreeLevelIndexMenuDetail($datas);

    	return Collection::make($templist)->toArray(); 
    }

    //仅获取三级菜单，也就是说本系统仅支持三级菜单
	public function CateThreeLevelIndexMenuDetail($arr)
	{
		$k=0;
		$Fmenu=array();                          //一级菜单
		$Omenu=array();                          //二级级菜单
		$Gmenu=array();                          //三级级菜单
		$TempOmenu=array();
		$TempGmenu=array();
		$ResultMenu=array();
		$tempMenu = array();

		foreach ($arr as $key => $value) 
		{
			if($value['level'] == '一级菜单')
			{
				if(!empty($Fmenu))
				{
					$tempMenu['father']  = $Fmenu;
					$tempMenu['son']     = $TempOmenu;
					$tempMenu['grandson']= $TempGmenu;
					$ResultMenu[$k]      = $tempMenu;
					$k++;
				}
				array_splice($tempMenu, 0, count($tempMenu));
				array_splice($Fmenu,    0, count($Fmenu));
				array_splice($Omenu,    0, count($Omenu));
				array_splice($Gmenu,    0, count($Gmenu));
				array_splice($TempOmenu,0, count($TempOmenu));
				array_splice($TempGmenu,0, count($TempGmenu));
				$Fmenu['id']         = $value['mid'];
				$Fmenu['name']       = $value['title'];
				$Fmenu['controller'] = $value['controller'];
				$Fmenu['hidden']     = $value['ishidden'];
				$Fmenu['status']     = $value['status'];
			}
			else 
			{
				if ($value['level'] == '二级菜单')
				{
					if(!empty($TempGmenu))
					{
						$tempMenu['father']  = $Fmenu;
						$tempMenu['son']     = $TempOmenu;
						$tempMenu['grandson']= $TempGmenu;
						$ResultMenu[$k]      = $tempMenu;
						$k++;
						array_splice($tempMenu, 0, count($tempMenu));
						array_splice($Omenu,    0, count($Omenu));
						array_splice($Gmenu,    0, count($Gmenu));
						array_splice($TempOmenu,0, count($TempOmenu));
						array_splice($TempGmenu,0, count($TempGmenu));
					}
					if((empty($TempGmenu))&&(!empty($TempOmenu)))
					{
						$tempMenu['father']  = $Fmenu;
						$tempMenu['son']     = $TempOmenu;
						$tempMenu['grandson']= $TempGmenu;
						$ResultMenu[$k]      = $tempMenu;
						$k++;
						array_splice($tempMenu, 0, count($tempMenu));
						array_splice($Omenu,    0, count($Omenu));
						array_splice($Gmenu,    0, count($Gmenu));
						array_splice($TempOmenu,0, count($TempOmenu));
						array_splice($TempGmenu,0, count($TempGmenu));
					}
					$Omenu['id']         = $value['mid'];
					$Omenu['name']       = $value['title'];
					$Omenu['controller'] = $value['controller'];
					$Omenu['method']     = $value['method'];
					$Omenu['hidden']     = $value['ishidden'];
					$Omenu['status']     = $value['status'];
					$TempOmenu           = $Omenu;
				}
				else                                             //三级菜单
				{
					if ($value['level'] == '三级菜单')
					{
						$Gmenu['id']         = $value['mid'];
						$Gmenu['name']       = $value['title'];
						$Gmenu['controller'] = $value['controller'];
						$Gmenu['method']     = $value['method'];
						$Gmenu['hidden']     = $value['ishidden'];
						$Gmenu['status']     = $value['status'];
						$TempGmenu[]         = $Gmenu;
					}
				}
			}	
		}

		if(!empty($Fmenu))
		{
			$tempMenu['father'] = $Fmenu;
			$tempMenu['son']    = $TempOmenu;
			$tempMenu['grandson']= $TempGmenu;
			$ResultMenu[$k] = $tempMenu;
		}
		return Collection::make($ResultMenu)->toArray(); 
	}


}