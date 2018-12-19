<?php
namespace app\admin\controller;

use think\Config;
use think\Session;
use think\Controller;
use think\cache\driver\Redis;

class Base extends Controller
{
	public function __construct()
	{
		parent::__construct();
		global $cacheRedis;                                          
        $cacheRedis = new Redis();
	}

	public function illegalLoading()
	{
		$userRole = Session::get('user_role');
		if(empty($userRole))
			$this->redirect('admin/Login/index');

	} 

	public function logined()
    {
    	if(Session::has('user_id'))
    	{
    		$this->redirect('admin/Index/index');
    	}
    }
    //空操作
    public function _empty($name)
    {
        if(Session::has('user_id'))
    	{
    		$this->redirect('admin/Error/action');
    	}
    	else
    	{
    		$this->redirect('admin/Login/index');
    	}
    }
    //空控制器
	/******************************************************************
	 * 加载配置文件
	 * @param string $file 配置文件名
	 ******************************************************************/
	public static function get_config($filename) 
	{
        return Config::get($filename);
	}

	/*****************************************************************
	 * 设置config文件     更新一维数组
	 * @param $config   配属信息    
	 * @param $filename 要配置的文件名称
	 *****************************************************************/
	function set_config($config, $filename="system") 
	{
		$configfile = ROOT_PATH.'application\extra'.DIRECTORY_SEPARATOR.$filename.'.php';
		if(is_writable($configfile))                                            //判断指定的文件是否可写。
		{
			$pattern = $replacement = array();
			foreach($config as $k=>$v) 
			{
				if(in_array($k,array('WEBSITE_TITLE','WEBSITE_KEYWORDS','WEBSITE_DESCRIPTION','JS_PATH','CSS_PATH','IMG_PATH','UPLOAD_PATH','UPLOAD_URL','WEBSITE_CORYRIGHT','WEBSITE_ICP','MAXLOGINFAILTIMES','MAIL_TYPE','MAIL_SERVER','MAIL_PORT','MAIL_FROM','MAIL_PASSWORD','MAIL_USER','WEB_CLOSE','MOOD1','MOOD2','MOOD3','MOOD4','MOOD5','MOOD6','MOOD7','MOOD8','MOOD9','MOOD10','attachment_stat','admin_log','gzip','errorlog','phpsso','phpsso_appid','phpsso_api_url','phpsso_auth_key','phpsso_version','connect_enable', 'admin_url'))) 
				{
					$v = trim($v);
					$configs[$k] = $v;
					$pattern[$k] = "/'".$k."'\s*=>\s*([']?)[^']*([']?)(\s*),/is";
		        	$replacement[$k] = "'".$k."' => \${1}".$v."\${2}\${3},";					
				}
			}
			$str = file_get_contents($configfile);                 //把整个文件读入一个字符串中
			$str = preg_replace($pattern, $replacement, $str);     //搜索str中匹配pattern的部分,以replacement进行替换。
		}
		dump($str);
		file_put_contents($configfile, $str);	
	}
	//更新一维和二维数组的配置文件
	public function update_config($new_config, $config_file = '') 
	{
		$str1 = "";
	    $config_file = ROOT_PATH.'application\extra'.DIRECTORY_SEPARATOR.$config_file.'.php';
	    if (is_writable($config_file)) 
	    {
	       $config = require $config_file;
	       $config = array_merge($config, $new_config);
	       $str = '<?php return [';
       	   foreach ($config as $key => $value) 
           {
           		if(is_array($value))
           		{
           			foreach ($value as $kkey => $vvalue) 
           			{
           				$str1 .= '\''.$kkey.'\''.'=>'.'\''.$vvalue.'\''.',';
           			}
           			$str .= '\''.$key.'\''.'=>'.'array('.$str1.')'.',';
           			$str1 = "";
           		}
           		else
           		{
           			$str .= '\''.$key.'\''.'=>'.'\''.$value.'\''.',';
           		}
       	   }
	       $str .= '];';
	       if(file_put_contents($config_file, $str)) 
	         return true;
	       else 
	         return false;
	    }   
	}

	function getdayNumber($data)
	{
	    $day   =  date( "j",$data);
	    return $day;
	}

}
