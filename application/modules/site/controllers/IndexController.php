<?php
class Site_IndexController extends Cl_Controller_Action_Index
{
    public function indexAction()
    {
        if($this->_u['id'] == GUEST_ID) {
            Bootstrap::$pageTitle = "Your website title";
            return;
        }
        else 
        {
            $daoSamx = Dao_Node_Samx::getInstance();
            $r = $daoSamx->findAll();
            if($r['success'] && $r['count'] > 0) {
                $this->setViewParam('list', $r['result']);
            }
        }
        Bootstrap::$pageTitle = "Site";
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
