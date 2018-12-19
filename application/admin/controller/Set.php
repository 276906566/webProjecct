<?php
namespace app\admin\controller;

use think\Request;
use think\Config;
use app\admin\controller\Base;
use app\admin\model\Website as WebsiteModel;

class Set extends Base
{
	protected $params;
	
	public function __construct()
	{
		parent::__construct();
		$this->illegalLoading();
	}
	
	public function index()
	{	
		$data = Base::get_config('website_config');

		$this->assign('info',$data);
		return $this->fetch('system_set');
	}

	public function save(Request $request)
	{
		if($request->isPost())
		{
			$data  = $request->param();
			$setting = config('website_config');
			$setting['WEBSITE_TITLE']       = trim($data['website_title']);
			$setting['WEBSITE_KEYWORDS']    = trim($data['website_Keywords']);
			$setting['WEBSITE_DESCRIPTION'] = trim($data['website_description']);
			$setting['CSS_PATH']            = trim($data['css_path']);
			$setting['JS_PATH']             = trim($data['js_path']);
			$setting['IMG_PATH']            = trim($data['img_path']);
			$setting['UPLOAD_PATH']         = trim($data['upload_path']);
			$setting['UPLOAD_URL']          = trim($data['upload_url']);
			$setting['WEBSITE_CORYRIGHT']   = trim($data['website_copyright']);
			$setting['WEBSITE_ICP']         = trim($data['website_icp']);
			$setting['MAXLOGINFAILTIMES']   = intval($data['maxloginfailedtimes']);
			$setting['MAIL_TYPE']           = intval($data['mail_type']);		
			$setting['MAIL_SERVER']         = trim($data['mail_server']);	
			$setting['MAIL_PORT']           = intval($data['mail_port']);	
			$setting['MAIL_FROM']           = trim($data['mail_from']);		
			$setting['MAIL_PASSWORD']       = trim($data['mail_password']);
			$setting['MAIL_USER']           = trim($data['mail_user']);		
			$setting['WEB_CLOSE']           = intval($data['web_close']);	
			
			Base::set_config($setting,'website_config');	

			$this->params['code']  =200;
            $this->params['msg']   ='操作成功';
            $this->params['data']  ='';
			                                       
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