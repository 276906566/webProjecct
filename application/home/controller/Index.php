<?php
namespace app\home\controller;

use think\Request;
use think\Session;
use think\Controller;

class Index extends Controller
{
	public function index()
	{
		return $this->fetch('index');
	}

	public function welcome()
	{
		return $this->fetch('index/welcome');
	}
}