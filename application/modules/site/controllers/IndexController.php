<?php
class Site_IndexController extends Cl_Controller_Action_Index
{
    public function indexAction()
    {
    	$filter = $this->getStrippedParam('filter','');
    	$this->setViewParam('active_filter', $filter);
    	$this->setViewParam('is_home', true);
    	if ($filter == '')
    		$filter = 'new';
    	$page = $this->getStrippedParam('page',1);
    	if ($page == '')
    		$page = 1;
    	
    	$this->setViewParam('is_widget', $this->getStrippedParam('is_widget'));
    	$this->setViewParam('page', $page);
    	$this->setViewParam('filter', $filter);
    	$ret = Dao_Node_Video::getInstance()->getVideoList($page, $filter);
        $this->setViewParam('list', $ret['result']);
        $total = ceil($ret['total'] / per_page());
        $this->setViewParam('total', $total);
        
        //Get new video
        $list = Dao_Node_Video::getInstance()->getVideoByType('new', 2);
        $this->setViewParam('newVideos', $list);
        
        //Get popular video
        $list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1);
        $this->setViewParam('hotVideos', $list);
        
        Bootstrap::$pageTitle = "Tổng hợp cover hay nhất, hài nhất";
    }
	public function errorAction()
	{
		
	}
    //==========================ADMIN==================================
    public function installAction()
    {
    	assure_perm('sudo');
    	$this->setLayout("admin");
    	if ($this->isSubmitted())
    	{
    		Cl_Dao_Util::getUserDao()->installSite();
    	}
    }
    
    public function adminAction()
    {
        assure_perm('sudo');
        $this->setLayout("admin");
        Bootstrap::$pageTitle = "Admin Panel";
    }
}
