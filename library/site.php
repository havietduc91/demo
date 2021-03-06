<?
//facebook parse int . comment,like,share
function fb_counter($url,$type)
{
	/**
	 * Add new node
	 * @param $url : url of site that we need count likes,comments,shares
	 * @param $type : string 'like' | 'comment' | 'share' | 'commentsbox'
	 * @return number of likes,comments,shares
	 * */
	if($type == 'like')
	{
		$like="<like_count>";
		$like1="</like_count>";
		$pos = 18;
	}
	if($type == 'share')
	{
		$like="<share_count>";
		$like1="</share_count>";
		$pos = 19;
	}
	if($type == 'commentsbox')
	{
		$like="<commentsbox_count>";
		$like1="</commentsbox_count>";
		$pos = 25;
	}
	if($type == 'comment')
	{
		$like="<comment_count>";
		$like1="</comment_count>";
		$pos = 21;
	}
	$addr="http://api.facebook.com/restserver.php?method=links.getStats&urls=".$url;
	//$page_source=file_get_contents($addr);
	$client = new Zend_Http_Client();
	$client->setMethod(Zend_Http_Client::GET);
	$client->setUri($addr);
	$response = $client->request();
	$page_source=$response->getBody();
	$page = htmlentities($page_source);
	$lik=strpos($page,htmlentities($like));
	$lik1=strpos($page,htmlentities($like1));
	$fullcount=strlen($page);
	$a=$fullcount-$lik1;
	$aaa=substr($page,$lik+$pos,-$a);
	$aaa1=substr($page,605,610);
	return $aaa;
}

//Check user's permission edit, delete video
function has_role_video($lu, $id){
	if(!$lu){
		$lu = Zend_Registry::get('user');
	}
	
	$where = array('id' => $id);
	$r = Dao_Node_Video::getInstance()->findOne($where);
	$v = array();
	
	if($r['success']){
		$v = $r['result'];
	}
	
	$uid = isset($v['u']['id']) ? $v['u']['id'] : 0;
	if($uid == $lu['id'])
		return true;
	
	return false;
}

function get_string_ytid($list){
	if(count($list) > 0){
		$strListVid = "'" . $list[0]['ytid']. "'";
		for ($i = 1;$i < count($list);$i ++){
			if($list[$i]['ytid'] != '')
				$strListVid = $strListVid . ",'" . $list[$i]['ytid']. "'";
		}
	}else{
		$strListVid = '';
	}
	
	return $strListVid;
}
/**return link of a user**/
function user_link($u = null, $absolute_url = false)
{
	if ($u == null)
		$u = Zend_Registry::get('user');
	$url = "/user/" . $u['iid'] . '-' .$u['lname'];
	if ($absolute_url)
	{
		return SITE_URL . $url;
	}
	else
		return $url;
}
function get_default_perms()
{
	return array('new_video', 'vote_video', 'update_own_video', 'delete_video');
}
function per_page()
{
	return 9;
}
function get_cache_dir()
{
	$dir = PUBLIC_PATH. '/cache/';//add folder caches
	if (getenv('SITE'))
		$dir = $dir . getenv('SITE') . '/';
	else
		$dir = $dir . CODENAME . '/';
	return $dir;
}
/**return link of a story **/
function node_link($type = 'video', $row, $type_redirect='view'){

	if ($type == 'video')
	{
		//if($type_redirect == 'upload'){
			//$link = '/video/view?id='. $row['id'];
		//}else{
			$link = '/video/'. $row['iid'].'-' . $row['slug'] .'.html';
		//}
	}
	
	elseif ($type == 'tag')
		$link = "/tagged/" . $row['slug'];
	else
		$link = "/$type/{$row['id']}";
	return $link;
}
function tag_link($tag)
{
	return "/tag/{$tag['slug']}";
}
function render_yt_embed_video($vid, $width = 420, $height = 315)
{
	return "<object width='$width' height='$height'>" .
	"<param name='movie' value='http://www.youtube.com/v/{$vid}?version=3&amp;hl=en_US'>".
	"</param>" .
	"<param name='allowFullScreen' value='true'></param>" .
	"<param name='allowscriptaccess' value='always'></param>" .
	"<embed src='http://www.youtube.com/v/" . "{$vid}?version=3&amp;hl=en_US' type='application/x-shockwave-flash'".
	"width='$width' height='$height' allowscriptaccess='always' allowfullscreen='true' wmode='transparent'
	></embed></object>";
}
function featured_tag()
{
	return '_featured';
}

// custom functions for demo
function display_avatar ($imgUrl, $size = 50, $atype = AS3_AVATAR_FOLDER)
{
	if(empty($imgUrl)) {
		return ($atype == AS3_AVATAR_FOLDER ? DEFAULT_AVATAR_URL : DEFAULT_ITEM_AVATAR_URL);
	}

	//remote url
	if(preg_match("/^(http|https)/", $imgUrl)) {
		return $imgUrl;
	}

	//local url
	if(strpos($imgUrl, dirname(APPLICATION_PATH)) !== false) {
		//local file, then strip the root dir
		$root = dirname(APPLICATION_PATH) . "/public";
		return str_replace($root, '', $imgUrl);
	}

	//$avatar = $atype . '/' . $size .'/'. $imgUrl;
	$avatar = AS3_ITEM_IMAGE_FOLDER . '/' . $size .'/'. $imgUrl;

	return AVATAR_PREFIX . '/' . str_replace("//", "/", $avatar);
}
//node link for remove staticache
function node_link_cache($type = 'video', $row){
	$link = array();
	if ($type == 'video')
	{
		$link[] = '/video/' . $row['iid'].'.html';
		$link[] = '/video/' . $row['iid'].'.html.json';
	}
	elseif ($type == 'tag')
		$link = "/tagged/" . $row['slug'];
	else
		$link = "/$type/{$row['id']}";
	return $link;
}
?>
