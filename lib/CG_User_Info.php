<?php 
require_once 'CG_DB.php';
require_once 'CG_Redis.php';

/**
*　获取或保存用户信息
*/
class CG_User_Info
{
	/**
	* 用户信息插入数据库
	*
	* @param array html信息
	* @return 
	*/
	public static function save_user_info($info)
	{
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
		return self::query($sql);
	}
	


	/**
	* 保存用户名到redis
	*/
	public static function save_username($usernames)
	{
		if (empty($usernames)
		{
			return FALSE;
		}
		if ($this->redis != NULL)
		{
			return $this->redis;
		}
		$redis = CG_User::get_redis();
		foreach ($usernames as $value)
		{
			$redis->zincrby('usernames',1,$value['username']);
		}
	}
	/**
	* 从redis中获取用户名,用来抓取用户信息
	*
	* @param  int 获取用户个数
	* @return array 
	*/
	public static function get_username($count = 60)
	{
		$limit = array('limit'=>array(0,$count-1));
		$redis = CG_User::get_redis();
		$usernames = $redis->zrangebyscore('usernames',0,0,$limit);
		if(empty($usernames))
		{
			return NULL;
		}
		else
		{
			return $usernames;
		}
	}	
	/**
	* 查询未抓去过信息的用户
	* @param int 获取记录数量
	* @return mixed
	*/
	public static function get_users($limit=20)
	{
		if (empty($limit))
		{
			return FALSE;
		}
		$sql = "SELECT `username` FROM `user` WHERE `used`=0 LIMIT 20";
		$result = self::query($sql);
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
		self::query($sql);
		// $result->free();
		return $res_arr;
	}

}
	