<?
function node_link($type, $node)
{
    return "/{$type}/view?id={$node['id']}";
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

?>
