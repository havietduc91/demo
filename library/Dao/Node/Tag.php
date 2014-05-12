<?php
/**
 * @author steve
 *
 */
class Dao_Node_Tag extends Cl_Dao_Node
{
    public $nodeType = 'tag';
    public $cSchema = array(
    		'id' => 'string',
    		'iid' => 'string',
    		'name' => 'string',
    		//'avatar' => 'string',
    		'slug' => 'string',
            'type' => 'int',
            'counter' => 'mixed'
    );
    
	protected function _configs(){
	    $user = Cl_Dao_Util::getUserDao()->cSchema;	    
    	return array(
    		'collectionName' => 'tags',
        	'documentSchemaArray' => array(
        		'iid' => 'string',
        	    'avatar' => 'string',
        		'name' => 'string', 
            	'slug' => 'string', // /tag/neither-nor
            	'ac' => 'string', //fullname, normalized. in lowercase "apple ipad 3 __electronics __computer
        		'content' => 'string',
    	        'content_uf' => 'string', //unfiltered content where <span class='item'> is converted to proper item links
            	 
    	        //in stead of an array is that, course almost never changes
        	    //and we can cache course data into Redis
        	
        		'type' => 'int', 
        		// 1 => nhan vat, 2 => ten show truyen hinh, 3 => 'text binh thuong'
        		'u' => $user, //who posted this	
        		'counter'	=>	array(
        			's' => 'int', //how many stories
    	        ),
        		/*
        		'comments' => array ( //comments can be embedded right into the node.
       					array(
        						'u'	=> $this->cSchema,
                                //'content'	=>	'string',
       					        'fcontent' => 'string', //filtered content
                                'ts'	=>	'mixed',
                                'id'	=>	'mixed',
                                'vc' => 'int', //vote count
                                'uv' => 'array' //array uid voted
                            )
                        ),
                 */
        		'ts' => 'int',
        		'status' => 'string', // approved, queued
        	),
    		'indexes' => array(
    				array(
    						array('iid' => 1),
    						array("unique" => true, "dropDups" => true)
    				),
    				array(
    						array('slug' => 1),
    						array("unique" => true, "dropDups" => true)
    				),
    				array(
    						array('name' => 1),
    						array("unique" => true, "dropDups" => true)
    				)
    				
    				
    		)
    			 
    			
    	);
	}
	
    /**
     * Add new node
     * @param post data Array $data
     */
	public function beforeInsertNode($data)
	{
	    if (!isset($data['status']))
	        $data['status'] = 'approved';
	    $data['ac'] = ac_item($data['name'], true);
	    if (!isset($data['slug']))
	    	$data['slug'] = $this->generateUniqueSlug(array($data['name']));	 
	    
	    if (!isset($data['iid']))
	    {
	    	$redis = init_redis(RDB_CACHE_DB);
	    	$data['iid'] = $redis->incr("tag:iid"); //unique course id
	    }
	     
        return array('success' => true, 'result' => $data);
	}
	
	public function afterInsertNode($data, $row)
	{
	    $this->addNodeToRedisSuggestion($row['id'], RDB_DICT_PREFIX, true);
	    $this->cacheNodeToRedis($row);
        return array('success'=> true, 'result' => $row);
	}
	
    /******************************UPDATE****************************/
    public function beforeUpdateNode($where, $data, $currentRow)
    {
        /*
         * You have $data['$set']['_cl_step'] and $data['$set']['_u'] available
         */
        if (isset($data['$set']['name']))
        {
            $data['$set']['ac'] = ac_item($data['$set']['name']);
            
            if ($data['$set']['name'] != $currentRow['name'])
                $data['$set']['slug'] = $this->generateUniqueSlug(array($data['$set']['name']));
        }
        return array('success' => true, 'result' => $data);
    }
    
	public function afterUpdateNode($where, $data, $currentRow)
    {
        //Update avatar for tag
        if(isset($data['$set']['images'][0])){
            $data['$set']['avatar'] = $data['$set']['images'][0]['id'] . '.' . $data['$set']['images'][0]['ext'];
            $update = array('$set' => array('avatar' => $data['$set']['avatar']
											));   
            $this->update($where,$update);
        }
        
        if (isset($data['$set']['ac']) && $data['$set']['ac'] != $currentRow['ac'])
        {
            $this->deleteNodeFromRedisSuggestion($currentRow);
            $this->addNodeToRedisSuggestion($currentRow['id'], RDB_DICT_PREFIX, true);
        }
       
        $this->cacheNodeToRedis($currentRow['id']);
        //Update cache objects everywhere
        $this->updateBasicInfo($currentRow['id']);
        return array('success' => true, 'result' => $data);    
    }   

    /**
     * @param TagId $id
     * @return multitype:boolean
     */
    public function updateBasicInfo($id)
    {
        $r2 = $this->getCacheObject($id);
        if (!$r2['success'])
        	return array('success' => false, 'err' => "tag $id does not exist");
        $cacheData = $r2['result'];
        
        $configs = array(
        		array (
        				'model' => 'Dao_Node_Story',
        				'fields' => array(
        						'tags' => 'tags.$'
        				)
        		),
        );
        
        $r = $this->updateBasicCacheInfo($id, $cacheData, $configs);
        return array("success" => true);        
    }

    /**
     * TODO: extract "related tags" from $row['tags'] or $row['r0'] or something
     * @param unknown_type $taggedTags
     * @param unknown_type $row
     */
    public function insertNewTags($tags, $row = array())
    {
    	$ret = array();
        foreach ($tags as $tag)
        {
        	if (isset($tag['_new'])) //@see suggest.php
        	{
        	    //The fisrt, init type = 3/format normal tag
        	    $tag['type'] = 3;
        	    //init counter.s = 1
        	    $tag['counter']['s'] = 1;
        	    
        	    $r = $this->insertNode($tag);
        		
        	    if ($r['success'])
        		{
        			$ret[] = $r['result'];
        			//TODO get slug & other stuff to update to other tags' cache
        			if (isset($tag['slug']) && $r['result']['slug'] != $tag['slug'])
        				$this->updateBasicInfo($r['result']['id']);
        		}
        	}
        	else {
        		/**
        		 * TODO: increase counter story in table tag
        		 * 		 Add fields to tag
        		 * 		 Run core calculate all counter.s of tag in table story contains this tag
        		 */
        	    
        	    //increase counter story in table tag
        		$where = array('id' => $tag['id']);
        		$update = array('$inc' => array('counter.s' => 1
											));
				$this->update($where, $update);
				
				//Add fields to tag
				$cond['where'] = $where;
				$tag = $this->findAll($cond);
				
				//Run core calculate all counter.s of tag in table story contains this tag
				$ret[] = $tag['result'][0];
				$this->updateCounterTagAllVideo($tag['result'][0]);
        	}
        	
        }
        return array('success' => true, 'result' => $ret);
    }
    
    
    /**
     * Delete a single node by its Id
     * @param MongoID $nodeId
     */
    public function afterDeleteNode($row)
    {
    	$this->deleteNodeFromRedisSuggestion($row);
    	$this->removeNodeFromRedis($row['id']);
    	$this->removeBasicCacheInfo($row['id']);
    	return array('success' => true);
    }
        
    public function removeBasicCacheInfo($id)
    {
        $configs = array(
            array (
            		'model' => 'Dao_Node_Story',
            		'fields' => array(
            				'tags'
            		),
            		'op' => 'pull'
            ),
        );
        
        $r = $this->deleteBasicCacheInfo($id, $configs);
    	return array('success' => true);
    }
    
    
	/******************************INSERT_COMMENT****************************/
    /**
     * You have $node = $data['_node'];
     */
    /*
	public function beforeInsertComment($data)
	{
	    $node = $data['_node'];
	    	        
        $data['node'] = array(
				'id'	=>	$data['nid'],
		);
		
        if (isset($node['name']) && !empty($node['name']))
            $data['node']['name']	=	$node['name'];
        else if (isset($node['content']))
            $data['node']['name']	= word_breadcumb($node['content'], CACHED_POST_TITLE_WORD_LENGTH);
	    
        if(is_null($data['attachments']) || $data['attachments'] == '')
        	unset($data['attachments']);
        
		return array('success' => true, 'result' => $data);
	}
	*/
    
	/**
     * You have $node = $data['_node'];
	 * Add new comment to a post
	 * @param POST data $data
	public function afterInsertComment($data, $comment)
	{
	    return array('success' => true, 'result' => $comment);
	}
	public function delComment($cid)
	{
		return Site_School_Dao_Comment_Tag::getInstance()->delete($cid);
	}
	 */
	

	
	/**
	 * Prepare data for new node insert
	 * @param Array $dataArray
	 */
	public function prepareFormDataForDaoInsert($dataArray = array(), $formName = "Tag_Form_New")
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
	public function afterInsertRelation($data, $row)
	{
		return array('success' => true, 'result' => $data);
	}
	
	
	public function filterNewObjectForAjax($obj, $formData)
	{
		return array('id' => $obj['id'], 'slug' => $obj['slug']);
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
	
	public function updateCounterTagAllVideo($tag){
	    /**
	     * 	Find all story:
	     * 		- tags has tag is $tag
	     * 			+ counter.s = this counter.s
	     * 			+ update tags of story
	     */
	    
	    $where = array('tags.id' => $tag['id']);
	    $cond['where'] = $where;
	    $st = Dao_Node_Video::getInstance()->findAll($cond);
	    
	    /**
	     * update tags:
	     * 		+ type if isset(type) == null
	     * 		+ counter.s if isset(counter.s) == null
	     */
	    $where3 = array('id'=>$tag['id']);
	    if(!isset($tag['counter']['s'])){
	    	$tag['counter']['s'] = count($st['result']);
	    }
	    
	    if(!isset($getTag['type']) ||  $t['type'] == ''){
	    	// defaul normal tag
	    	$update = array('$set'=>array('counter.s'=>count($st['result']), 'type'=>3));
	    }else{
	    	$update = array('$set'=>array('counter.s'=>count($st['result'])));
	    }
	    
	    //update count story of tag in table tag
	 	$this->update($where3,$update);
	    /**
	     * update tag in story_list 
	     */
	    
	    if($st['success']){
	        foreach ($st['result'] as $story){
	           //Update new counter.s
	           $tagsNew = array();
	           foreach ($story['tags'] as $t){
	               if($t['id'] == $tag['id']){
	                   $t['counter']['s'] = $tag['counter']['s'];
	                   if(!isset($t['type']) || $t['type'] == ''){
	                   		$t['type'] = 3; // defaul normal tag
	                   }
	               }   
	               
	               $tagsNew[] = $t;
	           }
	           
	           $where2 = array('id'=>$story['id']);
	           $update = array('$set' => array('tags' => $tagsNew
											));   	
               Dao_Node_Video::getInstance()->update($where2,$update);
	        }
	    }
	}
	
	public function getPopularTags(){
		$redis = init_redis(RDB_CACHE_DB);
		$popularTagsRedis = get_conf('popular_tags_redis', 'popularTagsRedis');
	
		$keys = $redis->keys($popularTagsRedis);
		foreach($keys as $key)
			$this->deleteRedisCache($key);
	
		$get_popular_tags_days = get_conf('get_popular_tags_days', 7);
		$ts = time() - 3600 * 24 * $get_popular_tags_days;
	
		$where = array('ts' => array('$gte' => $ts));
		$cond['where'] = $where;
	
		$r = Dao_Node_Story::getInstance()->findAll($cond);
		if($r['success']){
			$oldTags = array();
			$oldTagIids = array();
	
			$stories = $r['result'];
			foreach ($stories as $story){
				if(isset($story['tags']) && count($story['tags']) > 0){
					$tags = $story['tags'];
					list($oldTags, $oldTagIids) = $this->updatePopularTags($oldTags, $tags, $oldTagIids);
				}
			}
	
			//sort tags order by couter_s desc
			$counter_s = array();
			foreach ($oldTags as $key => $row)
			{
				$counter_s[$key] = $row['counter_s'];
			}
			array_multisort($counter_s, SORT_DESC, $oldTags);
				
			if(count($oldTags) > 0){
				$n = get_conf('number_popular_tags', 3);
				//set n popular tag into redis
				$i = 0;
				foreach ($oldTags as $tag){
					if($i <= $n)
						$redis->zAdd($popularTagsRedis, $tag['counter_s'], $tag['iid']);
					else
						break;
					$i ++;
				}
			}
				
				
			/**
			 * TEST::$currentPopularTagsRedis = $redis->zRange($popularTagsRedis, 0, -1, true);
			 */
		}
	}
	
	public function updatePopularTags($oldTags, $newTags, $oldTagIids){
		foreach ($newTags as $tag){
			if(!in_array($tag['iid'], $oldTagIids)){
				//add tag into $oldTags and add tagIid into $oldTagIids
				$oldTagIids[] = $tag['iid'];
				$tag['counter_s'] = 1;
				$insertTag = array(
						'iid' => $tag['iid'],
						'counter_s' => 1
				);
				$oldTags[] = $insertTag;
			}else{
				//update couter tag into oldTags
				$updateOldTags = array();
				foreach ($oldTags as $t){
					if($t['iid'] == $tag['iid']){
						$t['counter_s'] ++;
					}
	
					$updateOldTags[] = $t;
				}
	
				$oldTags = $updateOldTags;
			}
		}
	
		return array($oldTags, $oldTagIids);
	}
	
}