<?php
class Dao_Node_Video extends Dao_Node_Site
{
    public $nodeType = 'video';
    public $cSchema = array(
    		'id' => 'string',
    		'iid' => 'int',
    		'name' => 'string',
    		'avatar' => 'string',
    		'counter' => 'mixed',
    		'url' => 'string',
        	'ytid' => 'string',
        	'slug' => 'string',
        	'duration' => 'string',	
        	'ts' => 'int',
    		'ats' => 'int',
        	'status' => 'string',
        	'country' => 'string', //domestic|foreign
        	'is_original' => 'string',
    		//add other stuff u want
    );
        
    protected function relationConfigs($subject = 'user')
    {
    	if ($subject == 'user')
    	{
    		return array(
    				'1' => '1', //vote | like
    				'2' => '2', //follow
    				'3' => '3' , //flag as spam
    		);
    	}
    }
    
	protected function _configs(){
	    $user = Cl_Dao_Util::getUserDao()->cSchema;
	    $tag = Dao_Node_Tag::getInstance()->cSchema;
    	return array(
    		'collectionName' => 'video',
        	'documentSchemaArray' => array(
        		'iid' => 'int',
        		'name' => 'string', 
        		'ac_name' => 'string',
        		'avatar' => 'string',
        		'content' => 'string',
    	        'content_uf' => 'string', //unfiltered content where <span class='item'> is converted to proper item links 
        		'tags' => array( 
    	            $tag
    	        ),
        		'type' => 'int', // 1 => want item, 2 => own item, 3 => had item (reviews). If  9 => "uploaded photo"
        		'u' => $user, //who posted this	
        		'counter'	=>	array(
        			'c' => 'int', //comment
    	            'f' => 'int', //follow
    	            'r' => 'int', //recommend
    	            'l' => 'int', //likes
    	            'v' => 'int', //views on page
        			'vyt' => 'int', //views youtube
        			'hn'=> 'float',//hotness
        			'point' => 'float',
    	        ),
        		'url' => 'string',
        		'ytid' => 'string',
        		'slug' => 'string',
        		'duration' => 'string',	
        		'ts' => 'int',
        		'ats' => 'int',
        		'status' => 'string',
        		'country' => 'string', //domestic|foreign
        		'is_original' => 'string',
        	)
    	);
	}
	
    /**
     * Add new node
     * @param post data Array $data
     */
	public function beforeInsertNode($data)
	{
		if(isset($data['ts']) && $data['ts']){
			$data['ats'] = $data['ts'];
		}
		
		if(!isset($data['status'])) {
			$data['status'] = 'queued';
		}
		if(!isset($data['counter'])){
			$data['counter']=array('vote'=>0,
				'c' => 0, //comment
    	        'f' => 0, //follow
    	        'r' => 0, //recommend
    	        'l' => 0, //likes
    	        'v' => 0, //views on page 
				'vyt' => 0, //views youtube
        		'hn'=> 0,//hotness
			);
		}
		if (!isset($data['iid']))
		{
			$redis = init_redis(RDB_CACHE_DB);
			$data['iid'] = $redis->incr($this->nodeType . ":iid"); //unique node id
		}
		
		$url = $data['url'];
		parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
		$data['ytid'] = $my_array_of_vars['v'];
		
		//Get duration
		$url = "http://gdata.youtube.com/feeds/api/videos/". $data['ytid'];
		$doc = new DOMDocument;
		$doc->load($url);
		$title = $doc->getElementsByTagName("title")->item(0)->nodeValue;
		$data['duration'] = $doc->getElementsByTagName('duration')->item(0)->getAttribute('seconds');
		
		//Get views
		$JSON = file_get_contents("https://gdata.youtube.com/feeds/api/videos/{$data['ytid']}?v=2&alt=json");
		$JSON_Data = json_decode($JSON);
		$views = $JSON_Data->{'entry'}->{'yt$statistics'}->{'viewCount'};
		$data['counter']['vyt'] = $views;
		
		$data['ac_name'] = ac_item($data['name']); //accent name vietnamese
		
		if (isset($data['tags']) && count($data['tags']) > 0)
		{
			//unset  tag name 'featured' if user's not allowed
			foreach ($data['tags'] as $k => $tag){
				if($tag['name'] == featured_tag() && !has_perm('admin_video')){
					unset($data['tags'][$k]);
				}
			}
		
			$r = Dao_Node_Tag::getInstance()->insertNewTags($data['tags']);
			if (!$r['success'])
				return $r;
			else
				$data['tags'] = $r['result'];
		}
		
		//set slug for video
		if (!isset($data['slug']))
		{
			$tempSlug = Cl_Utility::getInstance()->generateSlug($data['name']);
			$data['slug'] = $this->generateUniqueSlug(explode('-', $tempSlug));
		}
        return array('success' => true, 'result' => $data);
	}
	
	public function afterInsertNode($data, $row)
	{
        return array('success'=> true, 'result' => $row);
	}
	
    /******************************UPDATE****************************/
    public function beforeUpdateNode($where, $data, $currentRow)
    {
    	//set approved timestamp
    	if ($data['$set']['_cl_step'] == 'status' && $data['$set']['status'] == 'approved'){
    		$data['$set']['ats'] = time();
    	}
    	
    	if(isset($data['$set']['name']) && $data['$set']['name'] != $currentRow['name']){
    		$data['$set']['ac_name'] = ac_item($data['$set']['name']); //accent name vietnamese    		
    	}
        /*
         * You have $data['$set']['_cl_step'] and $data['$set']['_u'] available
         */
        return array('success' => true, 'result' => $data);
    }
    
	public function afterUpdateNode($where, $data, $currentRow)
    {
        return array('success' => true, 'result' => $data);    
    }   
     
	/******************************INSERT_COMMENT****************************/
    /**
     * You have $node = $data['_node'];
     */
	public function beforeInsertComment($data)
	{
	    $node = $data['_node'];
	    	        
        $data['node'] = array(
            'id'	=>	$data['nid'],
		);
        
		$data['status'] = 'queued';
        
        if (isset($node['name']) && !empty($node['name']))
            $data['node']['name']	=	$node['name'];
        else if (isset($node['content']))
            $data['node']['name']	= word_breadcumb($node['content'], CACHED_POST_TITLE_WORD_LENGTH);
	    
        if(isset($data['attachments']) && (is_null($data['attachments']) || $data['attachments'] == ''))
        	unset($data['attachments']);
        
		return array('success' => true, 'result' => $data);
	}
		
	/**
     * You have $node = $data['_node'];
	 * Add new comment to a post
	 * @param POST data $data
	 */
	public function afterInsertComment($data, $comment)
	{
		// refresh & reculate hotness
		$node = $data['_node'];
		$this->incActivityCountAndCheckHotnessCache(array('ts' => time()),$node['iid'],$node['counter']['hn']);
	    return array('success' => true, 'result' => $comment);
	}
	
	public function beforeUpdateComment($where, $data, $row)
	{
        if($data['$set']['_cl_step'] == 'is_spam') {
            // incresase counter.spam
            $data['$inc'] = array('counter.spam' => 1);
        }
		return array('success' => true, 'result' => $data);
	}
	
	
	public function afterUpdateComment($where, $data, $row)
	{
        if(($data['$set']['_cl_step'] == 'is_spam') && 
                (in_array('admin', $data['$set']['roles']) || in_array('root', $data['$set']['roles']))
           )
        {
            // mark is_spammer
            $dataUpdate = array('$set' => array('is_spam' => 1));
            $cWhere = array('id' => $row['id']);
            Site_Codenamex_Dao_Comment_Video::getInstance()->update($cWhere, $dataUpdate);
            
            // TODO: 
        }
        
		return array('success' => true, 'result' => $data);
	}
	
	/**
	 * Delete a single node by its Id
	 * @param MongoID $nodeId
	 */
	public function afterDeleteNode($row)
	{
	    //delete all comments
	    
	    return array('success' => true, 'result' => $row);
	}
	
	
	/**
	 * Prepare data for new node insert
	 * @param Array $dataArray
	 */
	public function prepareFormDataForDaoInsert($dataArray = array(), $formName = "Video_Form_New")
	{
		return $dataArray;
	}	
	
	public function prepareCommentFormDataForDao($dataArray = array())
	{
		return $dataArray;
	}	

	/******************************RELATION*********************************/
	public function beforeInsertRelation($data)
	{
		return array('success' => true, 'result' => $data);
	}
	public function afterInsertRelation($data, $newRelations, $currentRow)
	{
		//Delete cache if exists
		parent::afterInsertRelation($data, $newRelations, $currentRow);
		 
		/**
		 *  Check status of video:
		 *  	if status is approved
		 *  		- remove cache home page hot
		 *  		- remove cache detail page hot
		*/
		if($data['o']['status'] == 'approved'){
			//Remove cache home page hot video.local/hot
			$this->incActivityCountAndCheckHotnessCache(array('ts' => time()),$data['o']['iid'],$data['o']['counter']['hn']);
		}
		 
		return array('success' => true, 'result' => $data);
	}
	public function afterDeleteRelation($currentRow, $rt, $newRelations = array())
	{
	    return array('success' => true);
	}
	
	public function filterNewObjectForAjax($obj, $formData)
	{
		return array('id' => $obj['id'] /*, 'slug' => $obj['slug'] */);
	}
	
	public function filterUpdatedObjectForAjax($currentRow, $step, $data, $returnedData)
	{
		$ret = array('id' => $currentRow['id']);
		return $ret;
		/*
		 if (isset($data['slug']))
			$ret['slug'] = $data['slug'];
		elseif (isset($currentRow['slug']))
		$ret['slug'] = $currentRow['slug'];
		return $ret;
		*/
	}
	
	public function updateView()
	{
		//$cond = array('where' => array());
		$cond['dao_class'] = 'Dao_Video';
		$this->doBatchJobs($cond, 0, array('updateViewForOneVideo'));
	}
	
	public function updateViewForOneVideo($video){
		$checkYtid = true;
		if(!isset($video['ytid']) || $video['ytid'] == ''){
			parse_str( parse_url( $video['url'], PHP_URL_QUERY ), $my_array_of_vars );
			$video['ytid'] = $my_array_of_vars['v'];
			$checkYtid = false;
		}
		 
		//Get views
		$JSON = file_get_contents("https://gdata.youtube.com/feeds/api/videos/{$video['ytid']}?v=2&alt=json");
		$JSON_Data = json_decode($JSON);
		$views = $JSON_Data->{'entry'}->{'yt$statistics'}->{'viewCount'};
		$where = array('id'=>$video['id']);
		 
		if($checkYtid){
			$update = array('$set'=>array(
						'counter.vyt' => $views,
					)
			);
		}else{
			$update = array('$set'=>array(
						'counter.vyt' => $views,
						'ytid' => $video['ytid'],
					)
			);
		}
		 
		$this->update($where, $update);
	}
	
	public function updateDuration()
	{
		//$cond = array('where' => array());
		$cond['dao_class'] = 'Dao_Video';
		$this->doBatchJobs($cond, 0, array('updateDurationForOneVideo'));
	}
	
	public function updateDurationForOneVideo($video){
		$checkYtid = true;
		if(!isset($video['ytid']) || $video['ytid'] == ''){
			parse_str( parse_url( $video['url'], PHP_URL_QUERY ), $my_array_of_vars );
			$video['ytid'] = $my_array_of_vars['v'];
			$checkYtid = false;
		}
			
		//Get duration
		$url = "http://gdata.youtube.com/feeds/api/videos/". $video['ytid'];
		$doc = new DOMDocument;
		$doc->load($url);
		$duration = $doc->getElementsByTagName('duration')->item(0)->getAttribute('seconds');

		if($checkYtid){
			$update = array('$set'=>array(
					'duration' => $duration,
				)
			);
		}else{
			$update = array('$set'=>array(
					'duration' => $duration,
					'ytid' => $video['ytid'],
				)
			);
		}
			
		$r = $this->update($where, $update);
	}
	
	public function getVideoByType($type, $limit, $ts){
		$ts = isset($ts) ? $ts : time();
		$list = array();
		if($type == 'new'){
			if(isset($ts) && $ts > 0){
				$order = array('ts'=>1);
			}else{
				$order = array('ts'=>-1);
			}
		}elseif ($type == 'hot'){
			$order = array('counter.vyt'=>-1);
		}
		
		if(isset($ts) && $ts > 0){
			$where = array('ts' => array('$gt' => $ts));
			$cond['where'] = $where;
		}
		
		$cond['order'] = $order;
		$cond['limit'] = $limit;
		$r = $this->find($cond);
		if($r['success'] && $r['count'] > 0) {
			$list = $r['result'];
		}else{
			//clip la moi nhat, nen khong co clip moi hon
			$where = array('ts' => array('$lt' => $ts));
			$cond['where'] = $where;
			$r = $this->find($cond);
			$list = $r['result'];
		}
		
		return $list;
	}
	
	public function getVideoList($page, $filter){
		$redis = init_redis(RDB_CACHE_DB);
		$where = array();
		if($filter == 'domestic'){
			$where = array('country'=> 'domestic');
			$order = array('ts' => -1);
		}
		if($filter == 'foreign'){
			$where = array('country'=> 'foreign');
			$order = array('ts' => -1);
		}
		if($filter == 'hot'){
			//array of top 100 hot iids
			$redisHotIidsKey = $this->redisHotIidKey();
			$currentHotIids = $redis->zRange($redisHotIidsKey, 0, -1, true);
			if (count($currentHotIids) == 0)
			{
				//TODO: generate top 100 hotness Iids
				//recalculate all hotness
				$this->refreshHotnessAll(array('ts' => time()),$redisHotIidsKey);
				$currentHotIids = $redis->zRange($redisHotIidsKey, 0, -1, true);
			}
			foreach ($currentHotIids as $i => $v)
			{
				$arrHot[$i]= (int) $i;
			}
			
			$currentHotIids=$arrHot;
			
			$where = array(
					'status' => 'approved', 
					'iid' => array('$in' => $currentHotIids)
			);
			$order = array('counter.hn' => -1);	
		}
		if($filter == 'vote'){
			$where = array('status'=> 'queued');
			$order = array('ts'=>-1);
		}else{
			$where = array('status'=> 'approved');
			$order = array('ts'=>-1); //TODO:$order = array('ats'=>-1); 
		}
		
		$cond['order'] = $order;
		$cond['limit'] = per_page();
		
		$cond['where'] = $where;
		$cond['page'] = $page;
		$cond['total'] = 1; //do count total
		
		$r = $this->find($cond);
		return $r;
	}
	
	public function getVideosByUser($user, $page){
		$where = array('u.id' => $user['id']);
		
		$cond['limit'] = per_page();
		$cond['where'] = $where;
		$cond['page'] = $page;
		$cond['total'] = 1; //do count total
		
		$r = $this->find($cond);
		return $r;
	} 
	
	public function addPlaylist($id){
		$lu = Zend_Registry::get('user');
		
		$where = array('id' => $id);
		$r = $this->findOne($where);
		
		if($r['success'] && $r['count'] > 0){
			$video = array(
				'id' => $r['result']['id'],
				'iid' => $r['result']['iid'],
				'name' => $r['result']['name'],
				'avatar' => $r['result']['avatar'],
				'counter' => $r['result']['counter'],
				'url' => $r['result']['url'],
				'ytid' => $r['result']['ytid'],
				'slug' => $r['result']['slug'],
				'duration' => $r['result']['duration'],
				'ts' => $r['result']['ts'],
				'status' => $r['status'],
				'country' => $r['result']['country'], //domestic|foreign
				'is_original' => $r['result']['is_original'],
				'u' => $r['result']['u']
			);
			
			$playlist = isset($lu['playlist']) ? $lu['playlist'] : array();
			$boolen = true;
			if(count($playlist) > 0){
				foreach ($playlist as $v){
					if($v['id'] == $id){
						$boolen = false;
						break;
					}
				}
				
				if($boolen){
					$playlist[] = $video;
				}
			}else{
				$playlist[] = $video;
			}
			
			if($boolen){
				$update = array('$set'=>array(
					'playlist' => $playlist
					)
				);
				
				$where = array('id' => $lu['id']);
				$r = Dao_User::getInstance()->update($where, $update);
			}else{
				$r = array('success' => false, 'err' => 'Video đã tồn tại trong danh sách ưa thích!');
			}
		}else{
			$r = array('success' => false, 'err' => 'Video không tồn tại!');
		}
		
		return $r;
	}
	
	public function fb_like_inc_point($iid,$rt)
	{
		$where = array('iid' => $iid);
		$r = Dao_Node_Video::getInstance()->findOne($where);
		$lu = Zend_Registry::get('user');
		//TODO: Vi day la like, comment tu facebook nen kha nang khong co tai khoan
		//Get default user => $lu = $defaulUser;
		if ($r['success'] && $r['count'] > 0)
		{
			$where1 = array('id' => $r['id']);
			//$actor, $action, $node, $subjectUser = array()
			if ($rt == 1 )//vote up
			{
				parent::updateUserKarmaAndNodePoint($lu,'video_voted_up',$r['result'],$r['result']['u']);
				//if voted up => increase 1 more vote
				$videoUpdate = array('$inc' => array('counter.l' => 1));
				$r2 = $this->update($where, $videoUpdate);
			} else if ($rt == 4 )//vote down
		    {
	            parent::updateUserKarmaAndNodePoint($lu, 'video_voted_down', $r['result'],$r['result']['u']);
		        $videoUpdate = array('$inc' => array('counter.l' => -1));
		        $r2 = $this->update($where, $videoUpdate);
		    }
		}
	}
	
	/**
	 * Removed folder by type = hot | new| best| vote
	 * @param unknown_type $type
	 */
	public function deleteStaticCacheOfType($type,$first,$last)
	{
		//Delete cache if exists
		$dir = get_cache_dir();
		$dirname = $dir . '/' . $type;
		for($i=$first;$i<=$last;$i++){
			if(file_exists($dirname."/".$i.'.html') && !is_dir($dirname."/".$i.'.html')){
				unlink($dirname."/".$i.'.html');
			}
			if(file_exists($dirname."/".$i.".json")){
				unlink($dirname."/".$i.".json");
			}
			if(is_dir($dirname."/".$i)){
				$this->rmdir_recursive($dirname."/".$i);
			}
		}
	}
	
	public function deleteStaticCache($row)
	{
		if (is_string($row))
		{
			$where = array('id' => $row);
			$t = $this->findOne($where);
			if ($t['success'])
				$row = $t['result'];
			else
				return false;
		}
		//Delete cache if exists
		$dir = get_cache_dir();
		$nodelinks = node_link_cache('video', $row);
		foreach( $nodelinks as $node)
		{
			$filename = $dir . $node;
			if(file_exists($filename)){
				unlink($filename);
			}
		}
		return array('success' => true);
	}
	/*************************** Calculate Hotness score*****************************************************/
	//TODO: Use doBatchJobs();
	public function calculateHotness($data)
	{
		$time = $data['ts'];
		$where = array('status' => 'approved');
		$cond['where'] = $where;
	
		$r= $this->findAll($cond);
	
		foreach ($r['result'] as $k => $row)
		{
			$row['counter']['hn']= $row['counter']['point'] / pow((($time-$row['ts'])/3600 + 2),1.5);
			$r['result'][$k]['mau']=pow((($time-$row['ts'])/3600 + 2),1.5);
			$this->update(array('id'=>$row['id']), $row);
		}
		return array('success'=>true,'result'=>$this->findAll($cond));
	}
	
	/**
	 * Recalculate all hotness score & cache top 200 into redis cache key "hotness:top_iids"
	 *
	 * @param unknown_type $data
	 * @param unknown_type $typeRedisHotIids //hotness:top_iids
	 */
	public function refreshHotnessAll($data,$typeRedisHotIids)
	{
		// reset hotness:activites
		$redis = init_redis(RDB_CACHE_DB);
		$redis->set("hotness:activites", 0);
	
		$keys = $redis->keys($typeRedisHotIids);
		foreach($keys as $key)
			$this->deleteRedisCache($key);
	
		// reculate hotness
		//$this->runBackground("calculateHotness", array($data));
		$this->calculateHotness($data);
	
		//cache top 100 into redis cache key "hotness:top_iids"
		$where = array('status' => 'approved');
	
		$order = array('counter.hn' => -1);
		$limit = 100;
	
		$cond['where'] = $where;
		$cond['order'] = $order;
		$cond['limit'] = $limit;
	
		$r = $this->find($cond);
		if ($r['success'])
		{
			foreach ($r['result'] as $video)
			{
				$redis->zAdd($typeRedisHotIids, $video['counter']['hn'], $video['iid']);
			}
		}
	
		return array('success' => true);
	}
	
	/**
	 *
	 * @param Int $type video type
	 */
	function redisHotIidKey()
	{
		return 'hotness:top_iids';
	}
	
	/**
	 * Calculate Hotness
	 *
	 * @param unknown_type $data
	 * @param unknown_type $currentHotIids
	 * @param unknown_type $iid
	 * @param unknown_type $typeRedisHotIids //hotness:top_iids
	 */
	public function calculate_hotness($data,$currentHotIids,$iid,$typeRedisHotIids){
		$time = $data['ts'];
		$ids = array();
	
		foreach ($currentHotIids as $i => $v)
		{
			$ids[]= (int) $i;
		}
	
		//Add iid into array to calculate hotness
		$ids[] = $iid;
			
		$where = array('iid' => array('$in' => $ids));
		$cond['where'] = $where;
		$r= $this->findAll($cond);
	
		foreach ($r['result'] as $k => $row)
		{
			$row['counter']['hn']= $row['counter']['point'] / pow((($time-$row['ts'])/3600 + 2),1.5);
			$r['result'][$k]['mau']=pow((($time-$row['ts'])/3600 + 2),1.5);
			$this->update(array('id'=>$row['id']), $row);
		}
	
		$redis = init_redis(RDB_CACHE_DB);
		$where = array('status' => 'approved');
	
		$order = array('counter.hn' => -1);
		$limit = 100;
	
		$cond['where'] = $where;
		$cond['order'] = $order;
		$cond['limit'] = $limit;
		$r = $this->find($cond);
	
		if ($r['success'])
		{
			foreach ($r['result'] as $video)
			{
				$redis->zAdd($typeRedisHotIids, $video['counter']['hn'], $video['iid']);
			}
		}
		return array('success'=>true,'result'=>$this->find($where));
	}
	
	/**
	 *
	 * Calculate hotness
	 *
	 * @param unknown_type $data
	 * @param unknown_type $iid  // iid of video relation
	 * @param unknown_type $hotNess
	 */
	public function incActivityCountAndCheckHotnessCache($data,$iid,$hotNess)
	{
		/**
		 * 			Haduc update 10.7.2013
		 * Check exit array redis with key values: hotness:top_iids
		 *  If array redis with key values: hotness:top_iids already exits
		 *  	- Add iids into array redis with key values: hotness:top_iids
		 *  	- Calculate hotness of member in array redis with key values: hotness:top_iids
		 *
		 *  Else if array redis with key values: hotness:top_iids not exits
		 *  	- Refresh all hotness cache
		 *  	- Calculate all hotness and add 100 member hotness with order -1 into array redis
		 *
		 */
		$typeRedisHotIids = $this->redisHotIidKey(); //hotness:top_iid
		$redis = init_redis(RDB_CACHE_DB);
	
		//array of top 100 hot iids
		$currentHotIids = $redis->zRange($typeRedisHotIids, 0, -1, true);
		if (count($currentHotIids) == 0)
		{
			//generate top 100 hotness Iids
			//recalculate all hotness
			$this->refreshHotnessAll(array('ts' => time()),$typeRedisHotIids);
			$currentHotIids = $redis->zRange($typeRedisHotIids, 0, -1, true);
		}
	
		$currentHotIids = array_reverse($currentHotIids,true);
		//Get list hotness old before relation
		foreach ($currentHotIids as $i => $v)
		{
			$hotnessOld[]= (int) $i;
		}
			
		$redis->zAdd($typeRedisHotIids, $hotNess, (int) $iid);
	
		$currentHotIids = $redis->zRange($typeRedisHotIids, 0, -1, true);
		$currentHotIids = array_reverse($currentHotIids,true);
	
		//calculate hotness 100 video in array redis
		$this->calculate_hotness($data,$currentHotIids,$iid,$typeRedisHotIids);
		$currentHotIids = $redis->zRange($typeRedisHotIids, 0, -1, true);

		$currentHotIids = array_reverse($currentHotIids,true);
		/*
		 * TODO: Cache
		//Get list hotness new after relation
		foreach ($currentHotIids as $i => $v)
		{
			$hotnessNew[]= (int) $i;
		}
	
		$result = array_diff_assoc($hotnessOld, $hotnessNew);
	
		if($result){
			// Get index fisrt different
			$k = 0;
			foreach($result as $key => $value){
				if($value){
					$k = $key;
					break;
				}
			}
	
			$pageChange = (int) ($k/10);
	
			// Removed cache hot from page $pageChange to page 10
			$this->deleteStaticCacheOfType($dir, $pageChange+1,10);
	
			// Removed cahce widget video hot if pagechange + 1 = 1
			if($pageChange == 0){
				$this->deleteStaticCacheOfType('widget/hot', $pageChange+1,2);
			}
	
		}
	
	
		$kRelation = array_search($iid, $hotnessOld);
		$situation = (int) ($kRelation/10);
	
		// Removed cache hot of page have element relation
		$this->deleteStaticCacheOfType($dir, $situation+1, $situation+1);
		// Removed cahce widget video hot if situation + 1 = 1
		if($situation == 0){
			$this->deleteStaticCacheOfType('widget/hot', $situation+1,2);
		}
		*/
	}
}
