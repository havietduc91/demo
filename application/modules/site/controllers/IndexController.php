<?php
class Site_IndexController extends Cl_Controller_Action_Index
{
    public function indexAction()
    {
        $daoVideo = Dao_Node_Video::getInstance();
        $r = $daoVideo->findAll();
        if($r['success'] && $r['count'] > 0) {
        	$this->setViewParam('list', $r['result']);
        }
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
