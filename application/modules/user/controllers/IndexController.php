<?php 
class User_IndexController extends Cl_Controller_Action_UserIndex
{
	public function init()
	{
		parent::init();
	}
	
	public function myClipAction()
	{
		$lu = Zend_Registry::get('user');
		Zend_Registry::set('viewuser', array());
		$page = $this->getStrippedParam('page',1);
		
		$r = Dao_Node_Video::getInstance()->getVideosByUser($lu, $page);
			
		if($r['success'] && $r['total'] > 0){
			$this->setViewParam('list', $r['result']);
		}else{
			$this->setViewParam('list', array());
		}
		
		$this->setViewParam('total', $r['total']);
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
	
	public function favouriteAction()
	{
		$lu = Zend_Registry::get('user');
		Zend_Registry::set('viewuser', array());
		$page = $this->getStrippedParam('page',1);
	
		$playlist = isset($lu['playlist']) ? $lu['playlist'] : array();
		$item_per_page = per_page();
		
		$newPlaylist = array();
		$start = ($page - 1) * $item_per_page + 1;
		$end = $page * $item_per_page + 1;
		
		if($start <= count($playlist)){
			for($i = $start;$i < $end;$i ++){
				$newPlaylist[] = $playlist[$i];
			}
		}
		
		$this->setViewParam('list', $newPlaylist);
	
		$this->setViewParam('total', count($playlist));
		$this->setViewParam('page', $page);
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
		
		if($user != array()){
			$r = Dao_Node_Video::getInstance()->getVideosByUser($user, $page);
			
			if($r['success'] && $r['total'] > 0){
				$this->setViewParam('uploadList', $r['result']);
			}else{
				$this->setViewParam('uploadList', array());
			}
		} 
		
		//Get new video
		$list = Dao_Node_Video::getInstance()->getVideoByType('new', 8, $row['ts']);
		$this->setViewParam('newVideos', $list);
		 
		//Get popular video
		$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1, $row['ts']);
		$this->setViewParam('hotVideos', $list);
		
		if($user != array()){
			Bootstrap::$pageTitle = $user['name'] . ' - Trang cá nhân';
		}else
			Bootstrap::$pageTitle = 'Trang cá nhân';	
	}
	
	public function anyOtherRequestAction()
	{
		
	}

	public function updateAction(){
		parent::updateAction();
		
		//Get new video
		$list = Dao_Node_Video::getInstance()->getVideoByType('new', 3, $row['ts']);
		$this->setViewParam('newVideos', $list);
			
		//Get popular video
		$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1, $row['ts']);
		$this->setViewParam('hotVideos', $list);
		
		Bootstrap::$pageTitle = 'Cập nhật tài khoản';
	}
	
	public function subscribeAction(){
		$id = (string) $this->getStrippedParam('id');

		if(!is_guest())
			$r = Dao_User::getInstance()->addSubcribe($id);
	}
	
	public function unsubscribeAction(){
		$id = (string) $this->getStrippedParam('id');
		
		if(!is_guest())
			$r = Dao_User::getInstance()->unsubcribe($id);
	}	
	
	public function loginAction(){
		parent::loginAction();
		
		//Get new video
		$list = Dao_Node_Video::getInstance()->getVideoByType('new', 3, $row['ts']);
		$this->setViewParam('newVideos', $list);
			
		//Get popular video
		$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1, $row['ts']);
		$this->setViewParam('hotVideos', $list);
	}
	
	public function registerAction(){
		parent::registerAction();
	
		//Get new video
		$list = Dao_Node_Video::getInstance()->getVideoByType('new', 3, $row['ts']);
		$this->setViewParam('newVideos', $list);
			
		//Get popular video
		$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1, $row['ts']);
		$this->setViewParam('hotVideos', $list);
	}
	
	public function searchRolesAction(){
		$this->setLayout("admin");
		
		parent::searchRolesAction();
	}
	
	public function newRoleAction(){
		$this->setLayout("admin");
		
		parent::newRoleAction();
	}
	
	public function updateRoleAction(){
		$this->setLayout("admin");
		
		parent::updateRoleAction();
	}
}
