<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$url = (!empty($_SERVER['HTTPS'])) ? "https://". DOMAIN : "http://".DOMAIN;
$callback = $url . '/user/oauth-success' ;//currentUrl();
$ssoOptions =
    array(
    'twitter' => array(
                        'signatureMethod' => 'HMAC-SHA1',
                        'callbackUrl' => $callback . '?login=twitter',
                        'siteUrl' => 'http://twitter.com/oauth',
                        'requestTokenUrl' => "https://api.twitter.com/oauth/request_token",
                        'authorizeUrl' => "https://api.twitter.com/oauth/authenticate",//authorize",
                        'accessTokenUrl' => "https://api.twitter.com/oauth/access_token",
                        'consumerKey' => 'FnenNvLWomFhYoqZX6b0Q',
                        'consumerSecret' => 'inz0BsEON5OCY3h7PPZqxJGTE5zvtaZdWB1BWhdhqo',
                        'uri' => 'http://twitter.com/account/verify_credentials.json',
    					'urlFriendIds' => 'https://api.twitter.com/1/friends/ids.json?cursor=-1&user_id=114981752',
    					'urlUserLookup' => 'http://api.twitter.com/1/users/lookup.json?user_id=36688537',
                        'statusUpdateUrl' => 'https://api.twitter.com/1/statuses/update.json',
                        'parameterPost' => array('trim_user' => true, 'include_entities'),
    					'profile_url' => 'https://twitter.com/account/redirect_by_id?id='
                    ),
    'facebook' => array(
                         'api'  => '483247035041551',
                         'secret' => 'c9c17095179c38458e265e8247730d56',
                         'cookie' => true,
                         'appId'  => '483247035041551',
                         'domain' => 'x.codersquare.com',
                         'callbackUrl' => $callback .'?login=facebook',
                         'permissions' => 'email,read_stream,publish_stream,offline_access,publish_actions',
                         'authorizeUrl' => "https://graph.facebook.com/oauth/authorize?client_id=%s&redirect_uri=%s&scope=%s",
                         'accessTokenUrl' => "https://graph.facebook.com/oauth/access_token?",
                         'dialogUrl' => 'https://www.facebook.com/dialog/oauth?client_id=%s&redirect_uri=%s&scope=%s',//&response_type=token', TODO: return via access_token?
                         'uri' => "https://graph.facebook.com/me?",
                         'postFeedUrl' => 'https://graph.facebook.com/me/feed?',
    		             'profile_url' => 'https://www.facebook.com/profile.php?id='
                    ),
		'yahoo' => array(
						'appID' => 'w7PZQI32',
                        'callbackUrl' => $callback . '?login=yahoo',
                        'requestTokenUrl' => "https://api.login.yahoo.com/oauth/v2/get_request_token",
				        'authorizeUrl' => "https://api.login.yahoo.com/oauth/v2/request_auth",
				        'accessTokenUrl' => "https://api.login.yahoo.com/oauth/v2/get_token",
                        'consumerKey' => 'dj0yJmk9dE1GV2tYUDlhTmhCJmQ9WVdrOWR6ZFFXbEZKTXpJbWNHbzlOemt6TmpFek5EWXkmcz1jb25zdW1lcnNlY3JldCZ4PWE5',
                        'consumerSecret' => 'bf308feeafbb61df34de2f7a8ff8ad90f4f6427a',
                        'uri' => 'http://social.yahooapis.com/v1/user/%s/profile',
                        'profile_url' => 'http://profile.yahoo.com/'
    	),
    	'google' => array(
				    	'requestScheme' => Zend_Oauth::REQUEST_SCHEME_HEADER,
				    	'version' => '1.0',
				    	'callbackUrl' => $callback . '?login=google',
				    	//'siteUrl' => 'https://www.google.com/accounts/',
				    	'requestTokenUrl' => 'https://www.google.com/accounts/OAuthGetRequestToken',
                        'userAuthorizationUrl' => 'https://www.google.com/accounts/OAuthAuthorizeToken',
                        'accessTokenUrl' => 'https://www.google.com/accounts/OAuthGetAccessToken',
				    	'scope' => array(
				    					/* 'http://www-opensocial.googleusercontent.com/api/people/', 
				    				     'https://www.googleapis.com/auth/userinfo#email',*/
				    				     'https://mail.google.com/mail/feed/atom',
				    				     'http://www.google.com/m8/feeds/',
				    			   ),
				    	'consumerKey' => 'x.codersquare.com',//'clu3.org',
				    	'consumerSecret' => '5Awnxa6Wi5fQDn8Tsgof1VDN',//'vylLLNzAa11vcETrgy79Qgap',
				    	//'uri' => 'https://www-opensocial.googleusercontent.com/api/people/@me/@self',
				    	'uri' => 'http://www.google.com/m8/feeds/contacts/default/full'
    	)
    );

?>
