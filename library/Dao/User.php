<?php
class Dao_User extends Cl_Dao_User
{
	public $cSchema = array(
			'id' => 'string',
			'iid' => 'int',
			'lname' => 'string',
			'name' => 'string',
			'avatar' => 'string',
			'background' => 'string',
			'url' => 'string',
			'ts' => 'int',
			'counter' => 'mixed', // image extension
			'playlist' => 'mixed',
	);
	/*
	public function findAll($cond = array(), $convertId = true, $filter = true)
	{
		$cond['limit'] = 10;
		return parent::find($cond);
	}
	*/
	
	protected $_oauthSchema = array(
			'id' => 'string',
			'name' => 'string',
			'access_token' => 'string',
			'avatar' => 'string',
			'mail' => 'string',
			'profile_url' => 'string',
			'duplicate_acc' => 'string' //id of an already-registered user id
	);
	
	protected function _configs()
	{
		$user = array(
			'id' => 'string',
			'iid' => 'int',
			'lname' => 'string',
			'name' => 'string',
			'avatar' => 'string',
			'background' => 'string',
			'url' => 'string',
			'ts' => 'int',
			'counter' => 'mixed', // image extension
		);
		
		$video = array(
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
			'status' => 'string',
			'country' => 'string', //domestic|foreign
			'is_original' => 'string',
		);
		
		return array(
			'collectionName' => 'users',
			'documentSchemaArray' => array(
				'iid' => 'int', //incremental uniq Id
				'name' => 'string',
				'lname' => 'string',
				'mail' => 'string',
				'mail_token' => 'string', ////mail verification token. If not empty => not yet verified
				'pass' => 'string',
				'ts' => 'int',
				'avatar' => 'string',
				'background' => 'string',
				'birthday' => 'string',
				'last_login' => 'int',
				'intro' => 'string',
				'playlist' => 'mixed',
				'token' => array(
					array(
						'token' => 'string',
						'ts' => 'int',
						'expire' => 'string'
					),
				),
				'device' => array(
						'id' => 'string',
						'type' => 'string' //[iphone | android]
				),
				'subscribe' => 'mixed',
				'status' => 'string', //unactivated|activated|banned
				'activation_code' => 'string',
				'roles' => 'array',
				'permissions' => 'array',
				'oauth' => array(
							'facebook' => $this->_oauthSchema,
							'twitter' =>  $this->_oauthSchema,
							'google' =>  $this->_oauthSchema,
							'yahoo' =>  $this->_oauthSchema,
					),
						
				'counter' => array(
					'notif' => 'int', //how many unread notif

					'redo' => 'int', // how many tasks which he is the implementer have been marked "redo"
					'p' => 'int', //number of stories posted
					'c' => 'int', //# of comments posted
					'k' => 'float', //karma: score of user, based on how many stories, comments, votes user receiv
					//'vote' => 'int', // number of vote | like
					'vd' =>'int', // number of vote down | unlike
				),
					
				'settings' => array(
					'language'=> 'string', //en or vi
					'notif_mail' => 'array', //when to send notifications via email
					'digest' => 'array', //[1,2] 1 For weekly, 2 for monthly
					'as' => 'array', // [1,2,3] . Activity stream settings. What to show on newsfeed:
					//1 for showing post activity, 2 for showing follow activity
					//3 for showing comment activity... By default, all will be inserted.
				),
				'help' => array( //set of settings where help on certain parts can be shown to guide users understand
				//the meaning of the icons, colors, flow....
				//by default, user will all have these settings. If dismissed, they will be gone 
					'tc' => 'int' , //task color. Blue are in charge, Black are involved
					'fl' => 'int', // first login (register)
				) ,
				'notif' => array(
							'0' => 'array.subdocument', // unread array of activities which are simple notices
							'1' => 'array.subdocument', // read array of activities which are simple notices
							'2' => 'array.subdocument', // unread activities which request attention
							'3' => 'array.subdocument', // read activities which request attention
							'4' => 'array.subdocument', //  important activities that should be alerted in top-fixed popup messages
							'5' => 'array.subdocument' //  important activities that should be
					), //array of activities
								
				'nrts' => 'int', //last notif read timestamp
				'nmts' => 'int', //last notif mailed timestamp user notifications sent via email. We might have daily digest
				//so we need to remember this timestamp
				'f' => 'int', //is fake or not
			),
			'indexes' => array(
					array(
							array(
									'lname' => 1, /* and something else here is fine for a couple*/
							),
							array("unique" => true, "dropDups" => true)
					),
					array(
							array(
									'mail' => 1,
							),
							array("unique" => true, "dropDups" => true)
					),
					array(
							array('iid' => 1),
							array("unique" => true, "dropDups" => true)
					)
			)
			
		);
	}
	
	public function beforeInsertUser($userData)
	{
		//check if user lname already exists or not
		if(isset($userData['mail']) && !isset($userData['lname'])) {
			$userData['lname'] = $userData['mail'];
		}
		
		if (isset($userData['lname']))
		{
			$where = array('lname' => $userData['lname']);
			$r = $this->findOne($where);
			if ($r['success'] && $r['count'] > 0)
			{
// 				return array('success' => true, 'result' => $r['result'], 'code' => 'existing');
				return array('success' => false, 'err' => "User with that login name already exists");
			}
		}
		$r = parent::beforeInsertUser($userData);
		if($r['success'])
			$userData = array_merge($userData, $r['result']);
		else
			return $r;
		
		//default user permissions
		if (!isset($userData['permissions'])) {
		    $userData['permissions'] = get_default_perms();
		}
		else 
		    $userData['permissions'] = array_merge($userData['permissions'], array());
		    
		$userData['help'] = array('tc' => 1, 'fl' => 1);
		if(!isset($userData['f']))
		  $userData['f'] = '0';
		if (!isset($userData['counter']))
			$userData['counter']= array(
						'p' => 0, //number of stories posted
						'c' => 0, //# of comments posted
						'k' => 0, //karma: score of user, based on how many stories, comments, votes user receiv
						'vote' => 0, // number of vote | like
						'vd' =>0, // number of vote down | unlike
			);
		
		if ($userData['lname'] == 'root')
		{
			$userData['iid'] = 0;
		}
		
		if (!isset($userData['iid']))
		{
			$redis = init_redis(RDB_CACHE_DB);
			$userData['iid'] = $redis->incr("user:iid"); //unique course id
		}
		
		if (!isset($userData['settings']['language']))
			$userData['settings']['language'] = get_conf('default_language', getenv('LANGUAGE') ? getenv('LANGUAGE') : 'en');
		
		return array('success' => true, 'result' => $userData);
	}
	
	public function afterInsertUser($userData, $row)
	{
		if (isset($row['mail'])) {
			if($row['status'] == 'unactivated')
				$template = 'mail_activation';
			else
				$template = 'welcome_user';
		
			if(isset($data['gen_pass']))
				$row['gen_pass'] = $data['gen_pass'];
			
			$language = $row['settings']['language'];
			//Dao_Mail::getInstance()->parseAndSendMail($template, '', $row['mail'], $row, $language);
		}
		 		
		return array('success' => true, 'result' => $row);
	}
	
	public function afterDeleteUser_($row)
	{
		return array('success' => true);
	}
	
	
    /** 
     * TODO: finish this
     * When user changes name or avatar, update other tables that cache this
     */
    public function updateBasicInfo_($uid, $data)
    {
        $configs = array(
        	array(
        		'model' => 'Dao_Node_Company',
        		'fields' => array(
					'u' => 'u'        				
        		)
        	),
        	array(
        		'model' => 'Dao_Node_Project',
        		'fields' => array(
        			'u' => 'u',
        			'participants' => 'participants.$',
        		)
        	),
            array(
            	'model' => 'Cl_Dao_Activity_Task',
                //'initOptions' => any params passed to the init() 
                'fields' => array( 
                	'actor.u' => 'actor.u', 
                	'object.u' => 'object.u', 
                	'context.u' => 'context.u'
                ),
            ),
        	array (
        			'model' => 'Dao_Node_Task',
        			'fields' => array(
        					'u' => 'u',
        					'participants' => 'participants.$',
        			)
        	),
            array (
            	'model' => 'Dao_Comment_Task',
                'fields' => array(
                    'u' => 'u',
                	'attachments.u' => 'attachments.$.u',
                ) 
            ),
        	array(
        		'model' => 'Cl_Dao_File',
        		'fields' => array(
        			'u' => 'u'
        		)
        	)       
        );
        
        $r = $this->updateBasicCacheInfo($uid, $data, $configs);
        return array("success" => true);
    }
    public function UpdateFakeFiledToAllUsers()
    {
    	$r = $this->findAll();
    	if($r['success'])
    	{
    		foreach($r['result'] as $row)
    		{
    			f($row);
    			if(!isset($row['f']))
    			{
	    			$where = array('id' => $row['id']);
	    			$newData = array('$set' => array('f' => 0));
	    			return $this->update($where, $newData);
    			}
    		}
    	}
    }
    
    public function addSubcribe($id){
    	$lu = Zend_Registry::get('user');
    	
    	$where = array('id' => $id);
    	$r = $this->findOne($where);
    	if($r['success'] && $r['count'] > 0){
    		$user = array(
    				'id' => $r['result']['id'],
    				'iid' => $r['result']['iid'],
    				'lname' => $r['result']['name'],
    				'avatar' => $r['result']['avatar'],
    				'background' => $r['result']['background'],
    				'url' => $r['result']['url'],
    				'ts' => $r['result']['ts'],
    				'counter' => $r['result']['counter'], // image extension
    		);
    			
    		$subscribe = isset($lu['subscribe']) ? $lu['subscribe'] : array();
    		$boolen = true;
    		if(count($subscribe) > 0){
    			foreach ($subscribe as $u){
    				if($u['id'] == $id){
    					$boolen = false;
    					break;
    				}
    			}
    	
    			if($boolen){
    				$subscribe[] = $user;
    			}
    		}else{
    			$subscribe[] = $user;
    		}
    			
    		if($boolen){
    			$update = array('$set'=>array(
    					'subscribe' => $subscribe
	    			)
    			);
    	
    			$where = array('id' => $lu['id']);
    			$r = Dao_User::getInstance()->update($where, $update);
    		}
    	}
    	
    	return $r;
    }
}
