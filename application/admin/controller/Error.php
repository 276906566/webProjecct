<?php
namespace app\admin\controller;
use think\Request;

class Error extends Base
{
	public function index()
    {
        $this->redirect('admin/Error/controll');
    }

    public function action()
    {
    	return $this->fetch('404');
    }
}
