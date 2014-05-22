<?php
/**
 * Remember you have    
 *  public $dao;
 *  public $node, $nodeUC; //node name : foo|post|item...

 * @author tran
 */ 
class Video_IndexController extends Cl_Controller_Action_NodeIndex 
{
    public function init()
    {
        //$this->daoClass = "Cl_Dao_Node_Video";
        //$this->commentDaoClass = "Cl_Dao_Comment_Video";
        
        /**
         * Chances to check for permission here if you like
         */
        parent::init();
        /**
         * Chances to change layout if you like
         */
    }

    public function indexAction()
    {

    }

    public function newAction()
    {
    	assure_perm('sudo');
    	$this->setLayout("admin");
        $this->genericNew("Video_Form_New", "Dao_Node_Video", "Node");
        if(isset($this->ajaxData)) {
            //command the form view to rediect if success
            if (isset($this->ajaxData['result'])) //success
            {
                if (is_preview())
                {
                    $this->setViewParam('row', $this->ajaxData['result']);
                    $this->setViewParam('is_preview', 1);
                    $this->_helper->viewRenderer->setNoRender(true);
                    $ret['data'] = $this->view->render('index/view.phtml');
                    $ret['success'] = true;
                    $ret['callback'] = 'populate_preview';
                    send_json($ret);
                    exit();
                }
                else 
                {
                	$this->ajaxData['callback'] = 'redirect';
                	$this->ajaxData['data'] = array('url' => node_link('video' , $this->ajaxData['result']));
                }
            }
        }
        Bootstrap::$pageTitle = 'Thêm video mới';
    }

    public function updateAction()
    {
    	assure_perm('sudo');
    	$this->setLayout("admin");
        /**
         * Permission to update a node is done in 
         * $Node_Form_Update form->customPermissionFilter()
         * Do not do it here
         * @NOTE: object is already filtered in Index.php, done in Cl_Dao_Node::filterUpdatedObjectForAjax()
         */
        $this->genericUpdate("Video_Form_Update", $this->daoClass ,"", "Node");
        Bootstrap::$pageTitle = 'Cập nhật video';
    }

    public function searchAction()
    {
    	assure_perm('sudo');
    	$this->setLayout("admin");
        assure_perm("search_video");//by default
        $this->genericSearch("Video_Form_Search", $this->daoClass, "Node");
        Bootstrap::$pageTitle = 'Quản lý video';        
    }
    
    public function searchVideosAction()
    {
    	$page = $this->getStrippedParam('page',1);
    	$name = get_value('ac_name','');
    	$ac_name = ac_item($name);
    	
    	$form = new Video_Form_Search();
    	$data = array(
    		'ac_name' => $ac_name,
    	);
    	 
    	$form->build($data);
    	$conditions = $form->buildSearchConditions();
    	 
    	$conditions['page'] = $page;
    	$conditions['limit'] = per_page();
    	$conditions['total'] = 1;
    	 
    	$dao = Dao_Node_Video::getInstance();
    	$r = $dao->findNode($conditions, true);

    	if($r['success'] && $r['total'] > 0){
    		$videos = $r['result'];
    	}else{
    		$videos = array();
    	}
    	
    	$this->setViewParam('list', $videos);
    	$total = ceil($r['total'] / per_page());
    	$this->setViewParam('total', $total);
    	$this->setViewParam('page', $page);
    	$this->setViewParam('ac_name', $ac_name);
    	
    	//Get new video
    	$list = Dao_Node_Video::getInstance()->getVideoByType('new', 2);
    	$this->setViewParam('newVideos', $list);
    	
    	//Get popular video
    	$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1);
    	$this->setViewParam('hotVideos', $list);
    	
    	Bootstrap::$pageTitle = 'Tìm kiếm cover video - ' . $name;
    }
    
    public function searchCommentAction()
    {
        assure_perm("search_video");//by default
        $commentClass =$this->commentDaoClass;
        $this->genericSearch("Video_Form_SearchComment", $commentClass, "");
        Bootstrap::$pageTitle = "Search " . $this->nodeUC . " Comments";        
    }
    
    public function viewAction()
    {
        //TODO Your permission here
        parent::viewAction();//no permission yet

        if ($row = $this->getViewParam('row')){
	        //Get list related story 5.9.2013
	        $list = array();
	        $tags = array();
	         
	        if(isset($row['tags'])){
	        	foreach ($row['tags'] as $tag){
	        		$tags[] = $tag['id'];
	        	}
	        	
	        	$where = array(
	        			'status' => 'approved',
	        			'tags.id' => array('$in' => $tags),
	        			'iid' => array('$ne' => $row['iid'])
	        	);
	        
	        	$cond['where'] = $where;
	        	$cond['limit'] = 8;
	        	$order= array('ts'=>-1);
	        	$cond['order'] = $order;
	        
	        	$list = Dao_Node_Video::getInstance()->find($cond);
	        	if($list){
	        		$this->setViewParam('related', $list['result']);
	        	}
	        }
        	Bootstrap::$pageTitle = $row['name'];
        	
        	//Get new video
			$list = Dao_Node_Video::getInstance()->getVideoByType('new', 3);
			$this->setViewParam('newVideos', $list);
        	
        	//Get popular video
        	$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1);
        	$this->setViewParam('hotVideos', $list);
        }else 
        	Bootstrap::$pageTitle = 'Chi tiết video';
    }
    
    public function deleteNodePermissionCheck($row)
    {
        if (has_perm("delete_video"))
            return array('success' => true);
        else 
            return array('success' => false);
    }
    public function commentAction(){
    	//$this->commentScript = "index/one-comment.phtml";
    	parent::commentAction();
    }
    
    //implements parent::newCommentPermissionCheck
    public function newCommentPermissionCheck($row)
    {
    	//TODO: Implement this
    	return has_perm("new_video_comment");
    }
    public function updateCommentAction()
    {
    	//$this->commentContentScript = "index/one-comment-content.phtml";
    	parent::updateCommentAction();
    }
    
    public function delCommentAction()
    {
    	parent::delCommentAction();
    }
    
    public function bulkDeleteAction()
    {
    	assure_role('admin_video');
    	$ids = $this->getStrippedParam('ids');
    	$in = explode(',', $ids);
    	$where = array('id' => array('$in' => $in));
    	Dao_Node_Video::getInstance()->delete($where);
    	$r = array('success' => true);
    	$this->handleAjaxOrMaster($r);
    }
    
    public function updateViewAction()
    {
    	Dao_Node_Video::getInstance()->updateView();
    	die('oki');
    }
    
    public function updateDurationAction()
    {
    	Dao_Node_Video::getInstance()->updateDuration();
    	die('oki');
    }
    
    public function tagAction()
    {
    	$tag = (string) $this->getStrippedParam('tag');
    	$where = array('slug'=>$tag);
    	$t = Dao_Node_Tag::getInstance()->findOne($where);
    	if($t['success']){
    		$this->setViewParam('tag', $t['result']);
    		
    		$where = array('tags.slug'=>$t['result']['slug']);
    		if($limit != '')
    			$cond['limit'] = $limit;
    		else
    			$cond['limit'] = per_page();
    		
    		$order = array('ts' => -1);
    		$cond['where'] = $where;
    		$cond['order'] = $order;
    		$cond['page'] = $page;
    		$cond['total'] = 1; //do count total
    		$r = Dao_Node_Video::getInstance()->find($cond);
    		if($r['success']){
    			$this->setViewParam('list', $r['result']);
    		}
    		
    		//Get new video
    		$list = Dao_Node_Video::getInstance()->getVideoByType('new', 3);
			$this->setViewParam('newVideos', $list);
			
			//Get popular video
			$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 2);
			$this->setViewParam('hotVideos', $list);
    		Bootstrap::$pageTitle = $t['result']['name'];
    	}else{
	    	Bootstrap::$pageTitle = 'Không tìm thất ' . $tag;
    	}
    }
}

