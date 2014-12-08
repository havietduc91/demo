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
    	$u = Zend_Registry::get('user');
    
    	if(has_role('sudo')){
	    	assure_perm('sudo');
	    	$this->setLayout("admin");
    	}else{
    		//Get new video
    		$list = Dao_Node_Video::getInstance()->getVideoByType('new', 3, $row['ts']);
    		$this->setViewParam('newVideos', $list);
    			
    		//Get popular video
    		$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1, $row['ts']);
    		$this->setViewParam('hotVideos', $list);
    	}
    	 
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
                	//TODO:$this->ajaxData['result'] (tim hieu no duoc tao ra nhu the nao)??
                	$id = $this->ajaxData['result']['id'];
                	$r = Dao_Node_Video::getInstance()->findOne(array('id'=>$id));
                	if($r['success']){
	                	$url = node_link('video' , $r['result'], 'upload');
                	}else{
                		$url = '/';
                	}
                	$this->ajaxData['data'] = array('url' => $url);
                }
            }
        }
        
        Bootstrap::$pageTitle = 'Thêm video mới';
    }

    public function updateAction()
    {
    	$lu = Zend_Registry::get('user');
    	
    	$id = $this->getStrippedParam('id');
    	if(!has_role_video($lu, $id))	
    		assure_perm('sudo');
    	
    	if(has_role('sudo'))
    		$this->setLayout("admin");
    	else{
    		//Get new video
    		$list = Dao_Node_Video::getInstance()->getVideoByType('new', 3, $row['ts']);
    		$this->setViewParam('newVideos', $list);
    		 
    		//Get popular video
    		$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1, $row['ts']);
    		$this->setViewParam('hotVideos', $list);
    	}
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
    	$iid = (string) $this->getStrippedParam('iid');
        //$slug = (string) $this->getStrippedParam('slug');
        if ($iid)
        	$where = array('iid' => $iid);
        //elseif ($slug)
        	//$where = array('slug' => $slug);
        	
    	$r = $this->dao->findOne($where, true /*convert id*/);
    	
    	if ($r['success'] && $r['count'] > 0)
    	{
    	     $this->setViewParam('row', $r['result']);
    	}   
    	else 
    	{
    		$this->_redirect("/");
    	}

        if ($row = $this->getViewParam('row')){
	        //Get list related video 5.9.2013
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
        	
        	//TODO: if wasn't original video => get original video, have to upload by admin 
        	$ori_viddeo = array();
        	if(!isset($row['is_original']) || $row['is_original'] == 'cover'){
        		$where = array(
        				'status' => 'approved',
        				'tags.id' => array('$in' => $tags),
        				'is_original' => 'original'
        		);
        		
        		$r = Dao_Node_Video::getInstance()->findOne($where);
        		if($r['success'] && count($r['result']) > 0){
        			$ori_viddeo = $r['result'];
        		}
        	}
        	
        	$this->setViewParam('ori_video', $ori_viddeo);
        	
        	//Get new video
			$list = Dao_Node_Video::getInstance()->getVideoByType('new', 15, $row['ts']);
			$this->setViewParam('newVideos', $list);
        	
        	//Get popular video
        	$list = Dao_Node_Video::getInstance()->getVideoByType('hot', 1, $row['ts']);
        	$this->setViewParam('hotVideos', $list);
        	
        	//Increase count view video
        	$whereUpdate = array('id'=>$row['id']);
        	$update = array('$inc'=>array('counter.v' => 1));
        	
        	//Get top user limit 10;
        	$list = Dao_User::getInstance()->getTopUser();
        	$this->setViewParam('topuser', $list);
        	Dao_Node_Video::getInstance()->update($whereUpdate, $update);
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
    
    public function addPlaylistAction(){
    	$id = (string) $this->getStrippedParam('id');
    	
    	$r = Dao_Node_Video::getInstance()->addPlaylist($id);
    	
    	//TODO: thong bao dang bi sai
    	return $r;
    	die();
    }
    
    public function manageVideoAction()
    {
    	$lu = Zend_Registry::get('user');
    	Zend_Registry::set('viewuser', array());
    	$page = $this->getStrippedParam('page',1);
    
    	$r = Dao_Node_Video::getInstance()->getVideosByUser($lu, $page);
    		
    	if($r['success'] && $r['total'] > 0){
    		$this->setViewParam('list', $r['result']);
    	}else{
    		$this->setViewParam('list', array());
    		$r['total'] = 0;
    	}
    	
    	$this->setViewParam('total', $r['total']);
    	$this->setViewParam('page', $page);
    	if($user != array()){
    		Bootstrap::$pageTitle = 'Quản lý clip - ' . $user['name'];
    	}else
    		Bootstrap::$pageTitle = 'Quản lý clip';
    }
    
    public function deleteAction()
    {
    	$lu = Zend_Registry::get('user');
    	 
    	$id = $this->getStrippedParam('id');
    	if(!has_role_video($lu, $id))
    		assure_perm('sudo');
    	
    	parent::deleteAction();
    	if ($this->ajaxData['success'])
    	{
    		$this->ajaxData['callback'] = 'reload_page';
    		$this->ajaxData['data'] = array('msg' => t('successful',1));
    	}
    }    
    
    //==============FB comment,like,share counter===========
    
    public function newFbCommentAction()
    {
    	// TODO: match this for different url schemes
    	$url = preg_replace('/#view$/', '',get_value('url'));
    	$id = get_value('id');
    	$fbc = fb_counter($url,'comment'); //fb comment
    	$daoClass = 'Dao_Node_Video';
    	$step = '';
    	$obj = 'Node';
    	$dao = $daoClass::getInstance();
    
    	$where = array('id' => $id);
    	$r = $dao->findOne($where);
    
    	if ($r['success'] && $r['count'] > 0)
    	{
    		$row = $r['result'];
    		$update = array('counter.c' => $fbc);
    		
    		$update = array('$set' => $update);
    		$r = $dao->update($where, $update);
    		$dao->deleteStaticCache($row);
    	}
    	$r = array('success' => true, 'result' => 'done!');
    	$this->handleAjaxOrMaster($r);
    }
    
    public function newFbLikeAction()
    {
    	 
    	$iid = $this->getStrippedParam('iid');
    	$id = $this->getStrippedParam('id');
    	$rt = $this->getStrippedParam('rt');
    	$uiid=$this->getStrippedParam('uiid');
    	if(is_rest() && $rt==1)
    	{
    		$options = array(
    				'subject' => 'user',
    				'object' => 'video',
    		);
    		$relationDao = Cl_Dao_Relation::getInstance($options);
    		$where=array(
    				's.iid'=>(string)$uiid,
    				'o.id'=>(string)$id,
    				'r.rt'=>$rt);
    		$cond['where']=$where;
    		$r=$relationDao->find($cond);
    		if($r['count']>0)
    		{
    			$r = array('success' => false, 'result' => 'Already like FB');
    			send_json($r);
    		}
    		else { //new relation
    			$object='video';
    			$data = array(
    					's' => Zend_Registry::get('user'),
    					'o' => array('id' => $id),
    					'r' => array(
    							'rt' => $rt
    					)
    			);
    			$requestParams = array(
    					'object'=>$object,
    					'rt'=>$rt,
    					'id'=>$id
    			);
    			$dao = Cl_Dao_Util::getDaoObject($object); //comment_samx => Cl_Dao_Comment_Samx
    			$r = $dao->insertRelation($data, $options, $requestParams);
    			if (!$r['success'])
    			{
    				$r = array('success' => false, 'result' => 'Error insert database');
    				send_json($r);
    			}
    			$dao_video=Dao_Node_Video::getInstance();
    			$rs=$dao_video->findOne(array('id'=>$id));
    			$iid=$rs['result']['iid'];
    		}
    	}
    	$dao_video=Dao_Node_Video::getInstance();
    	$dao_video->fb_like_inc_point($iid,$rt);
    	$dao_video->deleteStaticCacheOfType('vote',1,10);
    	$dao_video->deleteStaticCacheOfType('new',1,10);
    	$r = array('success' => true, 'result' => 'done!');
    	if(is_rest())
    	{
    		send_json($r);
    	}
    	$this->handleAjaxOrMaster($r);
    }
}

