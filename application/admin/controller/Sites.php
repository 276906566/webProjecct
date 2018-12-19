<?php
namespace app\admin\controller;

use think\Request;
use app\admin\controller\Base;
use app\admin\model\Sites as SitesModel;

class Sites extends Base
{
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function index()
	{
		$data = SitesModel::get(1);

		$this->assign('info',$data);

		return $this->fetch('sites_base');
	}
}