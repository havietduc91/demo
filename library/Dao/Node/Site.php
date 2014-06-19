<?php
/**
 * contains all the common stuff for the site
 * Such as : update concept, or logging user's activity
 * or for beforeInsertNode, set the iid if we decide to use iid for the whole site
 * @author steve
 *
 */
class Dao_Node_Site extends Cl_Dao_Node
{
    public $hotnessRanking = false;
    
    //Conf keys for different point threshold
    /** Kiem tra nguong cua video
     *  Neu status đang la queue thi se o muc vote
     *  Sau khi co duoc 1 so diem nhat dinh (defaul: 10) thi se chuyen len muc moi
     *  Sau khi co duoc 1 so diem nhat dinh duoc tong hop theo cac cong thuc tinh o duoi thi se chuyen len hot(duoc tinh theo hotness)
     *  Phan best la duoc tinh theo so diem 
     */
    public function thresholdKey($type)
    {
     
    }
    
    /** Cap nhat lại karma 
     * */
    public function afterInsertNode($data, $row)
    {
    	//if success. Insert the newly added concepts if any
    	/*if (isset($data['concepts']) && count($data['concepts']) > 0)
    	{
    		Dao_Node_Concept::getInstance()->insertNewTaggedConcepts($data['concepts']);
    	}*/
    	
    	$karmaKey = $this->karmaKey($row);
    	//credit user some karma for submitting a new node
    	$where = array('id' => $row['u']['id']);
    	$update = array(
       '$inc' => 
    	        array(
    	            //'counter.nr.' . $this->nodeType => 1,
    	            'counter.p'=> 1,
    			    //'counter.karma.' . $karmaKey =>
    			    'counter.k'=> 
    	                $this->karmaDelta($row['u'],$karmaKey, 'new_video', $row['u']),
    	));
    	
    	Dao_User::getInstance()->update($where, $update);
     }
    
    public function afterUpdateNode($where, $data, $currentRow)
    {
        /*if (isset($data['$set']['concepts']) && count($data['$set']['concepts']) > 0)
        {
        	Dao_Node_Concept::getInstance()->insertNewTaggedConcepts($data['$set']['concepts']);
        }*/
        
        // update user karma if change status to Approved.
        // This only happens if editor/admin approves a video
        // In this case, we should grant OP with a karma which is 
        // the same as OP voting up the video 
        if ($data['$set']['_cl_step'] == 'status' && $this->hotnessRanking)
        {
            //change from 'queued' to 'approved'
        	if (isset($data['$set']['status']) && 
                $data['$set']['status'] == 'approved' && 
        	    $currentRow['status'] != 'approved'
            )
        	{
        	    //insert relationship, as if this admin has voted up
        	    //this way, both the relationship exists
        	    //and the video point & user karma increases
        	    $relationData = array(
        	        'o' => $currentRow,
        	        's' => $data['_u'],
        	        'r' => array('rt' => 1)
        	    );
        	    $options['object'] = $this->nodeType;
        	    $options['subject'] = 'user';
        	    
        	    $this->insertRelation($relationData, $options); 
        	}
        	
        	//change from 'queued' to 'approved'
        	elseif (isset($data['$set']['status']) &&
        			$data['$set']['status'] == 'queued' &&
        			$currentRow['status'] != 'queued'
        	)
        	{
        	    //insert relationship, as if this admin has voted down
        	    //this way, both the relationship exists
        	    //and the video point & user karma decreases accordingly
        	    $relationData = array(
        	        'o' => $currentRow,
        	        's' => $data['_u'],
        	        'r' => array('rt' => 4)
        	    );
        	    $options['object'] = $this->nodeType;
        	    $options['subject'] = 'user';
        	    
        	    $this->insertRelation($relationData, $options); 
        	}
        }
        
        return array('success' => true, 'result' => $data);
    }

    /** This is generic function called each time a user interacts with a $node */
    /**
     * Note that for lesson for example, in some sites, probably we do 
     * not have karma at all. But we still call this func, just for simplicity 
     * 
     * @param unknown_type $actor
     * @param unknown_type $action
     * @param Row $node current node row
     * @param unknown_type $subjectUser
     * @return multitype:boolean
     */
    public function updateUserKarmaAndNodePoint($actor, $action, $node, $subjectUser = array())
    {
    	if ($subjectUser == array())
            $subjectUser = $node['u']; //defaulted to OP of $node
        $karmaKey = $this->karmaKey($node);
        $karmaDelta = $this->karmaDelta($actor, $karmaKey, $action, $subjectUser); 
        $pointDelta = $this->pointDelta($actor, $action, $node);

        // 1. Update Original Poster 's karma
        if ($karmaDelta != 0)
        {
            $where = array('id' => $subjectUser['id']);
        	$karmaUpdate = array(
    			'$inc' =>
        			array(
    					//'counter.karma.' . $karmaKey => $karmaDelta
    					'counter.k'  => $karmaDelta
        			));
        	Dao_User::getInstance()->update($where, $karmaUpdate );
        }
        //2. update video's point and other counters if neccessary
 
        	$videoUpdate = array(
    			'$inc' => array( 'counter.point' => $pointDelta	)
        	);
        	//TODO: If $node['status'] == 'queued' => move it to 'approved'
        	//if it has enough point
                if (!isset($node['counter']['point']))
                    $node['counter']['point'] = 0;
        	$newPoint = $node['counter']['point'] + $pointDelta;
        	
        	//We must config , e.g ebook:pointthreshold:approved
        	$queueThreshold = $this->karmaKey($node) . ":pointthreshold:approved";
        	//defaulted to 10 points
        	if ($node['status'] == 'queued' && $newPoint >= get_conf($queueThreshold, 10))
        	{
                $videoUpdate['$set'] = array('status' => 'approved','ats'=>time());   

                /*
                 * Tang karma cua nguoi upload khi video do tu vote qua new
                 */
                
            	$karmaKey = $this->karmaKey($node);
            	
            	//credit user some karma for submitting a new node
            	$where = array('id' => $node['u']['id']);
            	$update = array(
               '$inc' => 
            	        array(
            	            'counter.p'=> 1,
            			    'counter.k'=> 
            	                $this->karmaDelta($node['u'],$karmaKey, 'new_video', $node['u']),
            	));
            	
            	Dao_User::getInstance()->update($where, $update);
                /**
                 * status queued => approved => video change vote => new 
                 */

            	//Update approved timestamp
            	//Dao_Node_Video::getInstance()->initNewOrVoteCacheList('queued',$node['type']);
				//Dao_Node_Video::getInstance()->initNewOrVoteCacheList('approved',$node['type']);

                Dao_Node_Video::getInstance()-> deleteStaticCacheOfType('new',1,10);
                Dao_Node_Video::getInstance()-> deleteStaticCacheOfType('vote',1,10);
                
                //Delete cahce of widget video
                Dao_Node_Video::getInstance()-> deleteStaticCacheOfType('widget/new',1,2);
                Dao_Node_Video::getInstance()-> deleteStaticCacheOfType('widget/vote',1,2);
        	}
        	elseif ($node['status'] == 'approved' && $newPoint < get_conf($queueThreshold, 10))
        	{
        		$videoUpdate['$set'] = array('status' => 'approved');
        	}
        	
        	$this->update(array('id' => $node['id']), $videoUpdate);
                
        return array('success' => true);
    }
    
    public function afterInsertRelation($data, $newRelations, $node)
    {
        $actor = Zend_Registry::get('user');
        $node = $data['o'];

        if ($data['r']['rt'] == 1 )//vote up
        {
            if ($this->hotnessRanking)
                $this->updateUserKarmaAndNodePoint($actor, 'video_voted_up', $node);
                
            //if voted up => increase 1 more vote
        	$videoUpdate = array('$inc' => array('counter.vote' => 1));

        	$this->update(array('id' => $node['id']), $videoUpdate);
        }
        else if ($data['r']['rt'] == 4 )//vote down
        {
            if ($this->hotnessRanking)
                $this->updateUserKarmaAndNodePoint($actor, 'video_voted_down', $node);
      	    
            $videoUpdate = array('$inc' => array('counter.vd' => -1));
            $this->update(array('id' => $node['id']), $videoUpdate);
        }elseif ($data['r']['rt'] == 3 )//spam
        {
            $spam = $data['o']['counter']['spam'];
            if(isset($spam) && $spam >= get_conf('number_spam',2)){
                $this->removeCahceAndCahceRedis($data,$node);
                $videoUpdate = array(
                					'$inc' => array('counter.spam' => 1), 
                					'$set'=>array('status' => 'spammed')
                                );
            }else{
                $videoUpdate = array('$inc' => array('counter.spam' => 1));
            }
            
            // change status 
            $this->update(array('id' => $node['id']), $videoUpdate);
        }
        elseif ($data['r']['rt'] == 5 )// choosed pair 1
        {
            //if voted up => increase 1 more vote
            $videoUpdate = array('$inc' => array('counter.p1_vote' => 1));
            $r = $this->update(array('id' => $node['id']), $videoUpdate);
            if($r['success'])
                $this->removeCahceAndCahceRedis($data,$node);
        }
        elseif ($data['r']['rt'] == 6 )// choosed pair 1
        {
            //if voted up => increase 1 more vote
            $videoUpdate = array('$inc' => array('counter.p2_vote' => 1));
            $r = $this->update(array('id' => $node['id']), $videoUpdate);
            if($r['success'])
                $this->removeCahceAndCahceRedis($data,$node);
        }
    }
    
    
    
    /*************************************KARMA STUFF ************************************/
    
    /*
     * This is using HackerNews hotness ranking algorithm
     * Reference: http://eekip.com/task/5098c1239c513f792f000000
     *     
           http://amix.dk/blog/post/19574
           http://news.ycombinator.com/item?id=1781013
           http://blog.linkibol.com/2010/05/07/how-to-build-a-popularity-algorithm-you-can-be-proud-of/
           http://amix.dk/blog/post/19588 (Reddit algorithm)
     *
     * There are normally 3 kinds of categories
     *     - hot stories: stories that have counter.hn ordered -1
     *     - new stories: stories that are approved (by an admin or when it gets enough point)
     *     - queued stories: newly submitted stories that is by default 'queued' == status 
     *     
     * A video when moved queued to new will be removed from 'queued'
     * A video when moved from new to hot , can it appear in both??? TODO
     * 
     * 
     * @Note: 
     * Note the distinguish between user's karma and a node's point.
     * Video's point only depends on karma of users who interact with video (such as post, comment, vote, flag...)
     * 
     * Hotness is a function of (point, posted time)
     */
    /**
     * Get the karmaKey for node. This key will be
     * stored in user.counter.karma.$karmaKey
     * such as user.counter.karma.quora etc ...
     * @param unknown_type $row
     * @return string
     */
    public function karmaKey($row)
    {
    	if ($this->nodeType == 'quora')
    	{
    		$karmaKey = 'quora_' . $row['category'];
    	}
    	else //by default, user the nodeType for
    		$karmaKey = $this->nodeType;
    	return $karmaKey;
    }
    
    /**
     * Calculate user karma delta (+5 or -3) to be added/substracted
     * This can be a commonly used for at least quora/ebook/fun modules
     * where users can post their stuff
     * @See users.counter.karma config for definition of how karma is stored
     *
     *     to user.counter.karma.$karmaKey
    
     * $subjectUser: Owner of the node/comment
     * $actor: the user who initiated the action, such as posting or commenting
     *
     */
    public function calculateKarmaDelta($actorKarma, $action, $karmaKey)
    {
        $karmaDelta = 0;
    	switch ($action){
    		/************* NODE ******************/
    		//applicable to $actor;
    		//A new post will credit OP 5 points. He will get more and more
    		// for video being posted later
    		case 'new_video':
    			$karmaDelta = 5;
    			break;
			//applicable to $subjectUser;
    		case 'del_video':   //when user node is deleted.
    			$karmaDelta = -1 * $this->calculateKarmaDelta($actorKarma, 'new_video') - 1;
    			break;
    			
    		case 'video_voted_up':
    			$karmaDelta = 2 + log10($actorKarma + 1);
    			if($karmaDelta <0)
    				$karmaDelta =0;
    			break;
			case 'video_voted_down':
				$karmaDelta = -1 * $this->calculateKarmaDelta($actorKarma, 'video_voted_up',$karmaKey);
				break;
    				 
			/************* NODE COMMENT ****************/
			//applicable to $subjectUser;
    		case 'new_comment': //new node comment
    			$karmaDelta = 1;
    			break;
    		case 'del_comment': //node comment deleted by admin
    			$karmaDelta = -1.5;
    			break;
    		case 'comment_voted_up':
    			if($actorKarma >= 0)
    				$karmaDelta = log10($actorKarma + 1);
    			else
    				$karmaDelta = 0 ;
    			break;
    		case 'comment_voted_down':
    			$karmaDelta = -1 * $this->calculateKarmaDelta($actorKarma, 'comment_voted_up',$karmaKey);
    			break;
    		default:
    			break;
    	}
    	
    	return $karmaDelta;
    }
     
    public function karmaDelta($actor, $karmaKey, $action, $subjectUser)
    {
    	//what about when A,B,C voted a video
    	//Then the video is a bad video, admin voted down
    	// how do we deal with these cases? Decrease voters karma? Yes
    	//by how much? TODO: Probably no
    
    	//$karmaDelta is either applied to $actor or $subjectUser
    	
    	//$actorKarma = $actor['counter']['karma'][$karmaKey];
    	$actorKarma = $actor['counter']['k'];
    	$karmaDelta = $this->calculateKarmaDelta($actorKarma, $action, $karmaKey);
    
    	return $karmaDelta;
    }
        
    /*****************************STORY KARMA, a.k.a STORY Point . Hotness is another matter*****/
    /**
     * Node point delta 
     * Affects $node.counter.point
     * @return number
     */
    public function pointDelta($actor, $action, $node)
    {
        //$karmaKey = $this->karmaKey($node);
        //$actorKarma = $actor['counter']['karma'][$karmaKey];
        //$actorKarma = $actor['counter']['k'][$karmaKey];
        $actorKarma = $actor['counter']['k'];

        $point=0;
    	switch($action){
    		case 'video_voted_up':
    			$point = 2 + log10($actorKarma + 1); 
    			break;
    		case 'video_voted_down':
    			$point = -1 * $this->pointDelta($actor, 'video_voted_up', $node);
    			break;
    			
    		case 'comment_voted_up':
    			$point = log10($actor['counter']['k'] + 1);
    			break;
    		case 'comment_voted_down':
    		    $point = -1 * $this->pointDelta($actor, 'comment_voted_up', $node);
    		    break;
    		case 'new_comment':
    		    $point = 2 + log10($actorKarma + 1);
    		    break;
    		case 'del_comment':
    		    $point = -1 * $this->pointDelta($actor, 'new_comment', $node);
    		default:
    			break;
    	}
    	return $point;
    }
    
    
    /**
     * @param Row $row
     */
    public function removeStaticCache($row)
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
        $dir = realpath(PUBLIC_PATH  . '/cache/');//add folder caches
        $filename = $dir . node_link('video', $row);
        if(file_exists($filename)){
        	unlink($filename);
        }
        
    	return array('success' => true);
    }
    /**
     * facebook comment count
     */
   
    
    /**
     * TODO: if counter.spam > conf_number_spam
     * 		+ remove top_redis:video_new, .....
     * 		+ removed cache video in category vote
     */
    public function removeCahceAndCahceRedis($data,$node){
        if($data['o']['status'] == 'approved')
        {
            Dao_Node_Video::getInstance()-> deleteStaticCacheOfType('new',1,10);            
        }else{
            Dao_Node_Video::getInstance()-> deleteStaticCacheOfType('vote',1,10);
        }
        
        Dao_Node_Video::getInstance()->initNewOrVoteCacheList($data['o']['status'],0);
        Dao_Node_Video::getInstance()->initNewOrVoteCacheList($data['o']['status'],$data['o']['type']);
    }
}
