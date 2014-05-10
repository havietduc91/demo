<?php
class Dao_Node_Tag extends Cl_Dao_Node_Tag
{
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