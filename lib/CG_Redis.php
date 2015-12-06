<?php
require_once '../config/config.php';
class CG_Redis
{
	/**
	* 获取redis对象
	*
	* @return Redis
	*/
	public static get_redis()
	{
		$redis_conf = $globals['config']['redis'];
		$redis = new Redis();
		$res = $redis->connect($redis_conf['host'],$redis_conf['port']);
		return $res;
	}
}
