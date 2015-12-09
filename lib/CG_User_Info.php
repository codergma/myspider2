<?php 
require_once '/home/liubin/Downloads/myspider2/lib/CG_DB.php';
require_once '/home/liubin/Downloads/myspider2/lib/CG_Redis.php';
require_once '/home/liubin/Downloads/myspider2/lib/CG_Fetch_Html.php';
require_once '/home/liubin/Downloads/myspider2/lib/CG_Parse_Html.php';

/**
*　获取或保存用户信息,主要与MySQL和Redis
*/
class CG_User_Info
{
	/**
	* @var array
	*/
	private $usernames = array();
	/**
	* 用户信息插入数据库
	*
	* @param array  $info 待插入数据库数组
	* @return mixed
	*/
	public function save_user_info($info)
	{
		if(empty($info))
		{
			return FALSE;
		}
		$res_arr['`id`'] = 0;
		//　构造INSERT语句
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

		//　构造　ON DUPLICATE KEY UPDATE语句
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
		
		return CG_DB::query($sql);
	}
	

	/**
	* 从redis中获取用户名,用来抓取用户信息
	*
	* @param  int $count 获取用户个数
	* @return array 
	*/
	public  function get_redis_user($count = 60)
	{
		$redis = CG_Redis::get_redis();
		$limit = array('limit'=>array(0,$count-1));
		$this->usernames = $redis->zrangebyscore('usernames',1,1.0E+8,$limit);
		while(empty($this->usernames))
		{
			$this->_fetch_follow();
			$this->usernames = $redis->zrangebyscore('usernames',1,1.0E+8,$limit);
		}
		$result = array_splice($this->usernames,0,$count);
		array_walk($result, '_walk_callback');
		return $result; 
	}	

	/**
	* 从MySQL中，查询未以此为标准抓取关注者的用户
	* 
	* @param int $limit 获取记录数量
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
	*
	* @return mixed
	*/
	private function _fetch_follow()
	{
		$count = 20;
		$usernames = $this->_get_sql_users($count);
		if (empty($usernames))
		{
			file_put_contents('/home/liubin/Downloads/myspider2/log/'.date('Y-m-d H:i:s').'.log', 
							'数据库中没有可用用户',FILE_APPEND);
			return FALSE;
		}

		$followees_urls = array();
		$followers_urls = array();
		foreach ($usernames as $username)
		{
			$followees_urls[] = "http://www.zhihu.com/people/{$username}/followees";
			$followers_urls[] = "http://www.zhihu.com/people/{$username}/followers";
		}

		$cookie_path = '/home/liubin/Downloads/myspider2/config/cookie.txt';
		$cookie = file_get_contents($cookie_path);
		$useragent   = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';

		//抓取followees
		$fetch_followees   =  new CG_Fetch_Html($followees_urls,"followees_callback");
		$fetch_followees->cookie = $cookie;
		$fetch_followees->useragent  = $useragent;
		$fetch_followees->fetch_html();


		//抓取followers
		$fetch_followers   =  new CG_Fetch_Html($followers_urls,'followers_callback');
		$fetch_followers->cookie = $cookie;
		$fetch_followers->useragent  = $useragent;
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
		if (!empty($response))
		{
			$info = CG_Parse_Html::parse_username($response,'followers');

			if(!empty($info))
			{
				save_to_redis($info);
			}
		}
	}
	function _walk_callback($value,$key)
	{
		$redis = CG_Redis::get_redis();
		$redis->zincrby('usernames',-1.0E+10,$value);
	}
