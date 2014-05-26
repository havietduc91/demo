<?php 
class User_IndexController extends Cl_Controller_Action_UserIndex
{
	public function init()
	{
		parent::init();
	}
	
	public function viewAction()
	{
		$iid = $this->getStrippedParam('iid');
		$name = $this->getStrippedParam('name','');
		
		$where = array();
		if(isset($iid)){
			$where = array('iid' => $iid);
		}else if(isset($name)){
			$where = array('name' => $name);
		}
		
		$user = array();
		
		if($where != array()){
			$r = Dao_User::getInstance()->findOne($where);
			if($r['success']){
				$user = $r['result'];
			}
		}

		Zend_Registry::set('viewuser', $user);
		$page = $this->getStrippedParam('page',1);
		
		//if($user != array()){
			$r = Dao_Node_Video::getInstance()->getVideosByUser($user, $page);
			
			if($r['success'] && $r['total'] > 0){
				$this->setViewParam('list', $r['result']);
			}else{
				$this->setViewParam('list', array());
			}
		//} 
		
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
