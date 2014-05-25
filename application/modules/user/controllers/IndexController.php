<?php 
class User_IndexController extends Cl_Controller_Action_UserIndex
{
	public function init()
	{
		parent::init();
	}
	
	public function viewAction()
	{
		parent::viewAction();
		$user = Zend_Registry::isRegistered('viewuser') ? Zend_Registry::get('viewuser') : array(); 
		
		//Get new video
		$list = Dao_Node_Video::getInstance()->getVideoByType('new', 3, $row['ts']);
		$this->setViewParam('newVideos', $list);
		 
		//Get popular video
		$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1, $row['ts']);
		$this->setViewParam('hotVideos', $list);
		
		if($user != array()){
			Bootstrap::$pageTitle = 'Trang cá nhân - ' . $user['name'];
		}else
			Bootstrap::$pageTitle = 'Trang cá nhân';	
	}
	
	public function anyOtherRequestAction()
	{
		
	}    
}
