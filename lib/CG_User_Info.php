<?php 
require_once '/home/liubin/Downloads/myspider2/lib/CG_DB.php';
require_once '/home/liubin/Downloads/myspider2/lib/CG_Redis.php';
require_once '/home/liubin/Downloads/myspider2/lib/CG_Fetch_Html.php';
require_once '/home/liubin/Downloads/myspider2/lib/CG_Parse_Html.php';

/**
*　获取或保存用户信息
*/
class CG_User_Info
{
	private $usernames = array();
	/**
	* 用户信息插入数据库
	*
	* @param array html信息
	* @return 
	*/
	public function save_user_info($info)
	{
		$sql = "INSERT INTO `user` (`id`,`username`,`headline`,`headimg`,`location`,`business`,`gender`,`employment`,`position`,`education`,`education_extra`,`weibo`,`description`,`followees`,`followers`,`followed`,`topics`,`pv`,`asks`,`answers`,`posts`,`collections`,`logs`,`votes`,`thanks`,`favs`,`shares`)VALUES(0,'yunshu','阿里巴巴集团 资深安全专家，http://www.icylife.net','https://pic1.zhimg.com/32d3a53cb425fdae5d9ef0297bbbb080_l.jpg','杭州','互联网',1,'阿里巴巴集团','资深安全专家','武汉科技大学','国际经济与贸易专业','http://weibo.com/pstyunshu','等闲变却故人心，却道故人心易变',197,60890,1,35,17627,3,47,16,1,14,26821,2100,1284,120) ON DUPLICATE KEY UPDATE `username`='yunshu',`headline`='阿里巴巴集团 资深安全专家，http://www.icylife.net',`headimg`='https://pic1.zhimg.com/32d3a53cb425fdae5d9ef0297bbbb080_l.jpg',`location`='杭州',`business`='互联网',`gender`=1,`employment`='阿里巴巴集团',`position`='资深安全专家',`education`='武汉科技大学',`education_extra`='国际经济与贸易专业',`weibo`='http://weibo.com/pstyunshu',`description`='等闲变却故人心，却道故人心易变',`followees`=197,`followers`=60890,`followed`=1,`topics`=35,`pv`=17627,`asks`=3,`answers`=47,`posts`=16,`collections`=1,`logs`=14,`votes`=26821,`thanks`=2100,`favs`=1284,`shares`=120";
		CG_DB::query($sql);
		return ;
		if(empty($info))
		{
			return FALSE;
		}

		$res_arr = array();
		$res_arr['`id`'] = 0;
		foreach ($info as $key => $value)
		{
			$key = "`$key`";
			if (is_string($value))
			{
				$value = stripslashes($value);
				$value = addslashes($value);
				$value = "'$value'"; 
			}
			$res_arr[$key] = $value;
		}

		$keys = implode(array_keys($res_arr),',');
		$vals = implode(array_values($res_arr),',');
		$keys = '('.$keys.')';
		$vals = '('.$vals.')';

		$update = NULL;
		foreach ($res_arr as $key => $value)
		{
			if ($key != "`id`")
			{
				$update .= $key.'='.$value.',';
			}
		}
		$update = substr($update,0, -1);
		$sql = "INSERT INTO `user` ";
		$sql .= $keys.'VALUES'.$vals;
		$sql .=" ON DUPLICATE KEY UPDATE ".$update;
        file_put_contents('/home/liubin/Desktop/log.txt',$sql);
		return CG_DB::query($sql);
	}
	

	/**
	* 从redis中获取用户名,用来抓取用户信息
	*
	* @param  int 获取用户个数
	* @return array 
	*/
	public   function get_redis_user($count = 60)
	{
		$redis = CG_Redis::get_redis();
		$limit = array('limit'=>array(0,$count-1));
		$this->usernames = $redis->zrangebyscore('usernames',1,1.0E+8,$limit);
		while(empty($this->usernames))
		{
			$this->_fetch_follow();
			$this->usernames = $redis->zrangebyscore('usernames',1,1.0E+8,$limit);
		}
		return array_splice($this->usernames,0,$count);

	}	

	/**
	* 从MySQL中，查询未以此为标准抓取关注者的用户
	* 
	* @param int 获取记录数量
	* @return mixed
	*/
	private function _get_sql_users($limit=20)
	{
		if (empty($limit))
		{
			return FALSE;
		}
		$sql = "SELECT `username` FROM `user` WHERE `used`=0 LIMIT 20";
		$result = CG_DB::query($sql);
		$res_arr = array();
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$res_arr[] = $row['username'];
			}
		}
		$sql = "('".implode($res_arr,"','")."')";
		//标记为已经抓去过信息
		$sql = "UPDATE `user` SET `used`=1 WHERE `username` IN ".$sql;
		CG_DB::query($sql);
		// $result->free();
		return $res_arr;
	}

	/**
	* 关注者，关注了用户名保存用户名到redis中，下一步用来抓取信息
	*/
	private function _fetch_follow()
	{
		$count = 20;
		$usernames = $this->_get_sql_users($count);
		if (empty($usernames))
		{
			file_put_contents('/home/liubin/Downloads/myspider2/log/'.date('Y-m-d H:i:s').'.log', 
							'数据库中没有可用用户',FILE_APPEND);
			return ;
		}

		$followees_urls = array();
		$followers_urls = array();
		foreach ($usernames as $username)
		{
			$followees_urls[] = "http://www.zhihu.com/people/{$username}/followees";
			$followers_urls[] = "http://www.zhihu.com/people/{$username}/followers";
		}

		$cookie_path = '/home/liubin/Downloads/myspider2/config/cookie.txt';
		$useragent   = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';

		//抓取followees
		$fetch_followees   =  new CG_Fetch_Html($followees_urls,"followees_callback",$cookie_path,$useragent);
		$fetch_followees->fetch_html();


		//抓取followers
		$fetch_followers   =  new CG_Fetch_Html($followers_urls,'followers_callback',$cookie_path,$useragent);
		$fetch_followers->fetch_html();
	}
	
}
	function save_to_redis($usernames)
	{
		if (empty($usernames))
		{
			return FALSE;
		}
		$redis = CG_Redis::get_redis();
		foreach ($usernames as $value)
		{
			$redis->zincrby('usernames',1,$value['username']);
		}
	}
	// 回调函数，用来处理关注了页面信息
	function followees_callback($response)
	{
		if (!empty($response))
		{
			$info = CG_Parse_Html::parse_username($response,'followees');

			if(!empty($info))
			{
				save_to_redis($info);
			}
		}
	}
	// 回调函数，用来处理关注者页面信息
	function followers_callback($response)
	{
//        file_put_contents('/home/liubin/Downloads/myspider2/log/test.html',$response,FILE_APPEND);
		if (!empty($response))
		{
			$info = CG_Parse_Html::parse_username($response,'followers');

			if(!empty($info))
			{
				save_to_redis($info);
			}
		}
	}
	function _walk_callback($value,$key,$redis)
	{
		$redis->increby('usernames',-1.0E+10,$value);
	}
