<?php
require_once '/home/liubin/Downloads/myspider2/config/config.php';

/**
* Redis连接类
*/
class CG_Redis
{

	/**
	* 获取redis对象
	*
	* @return Redis
	*/
	public static function get_redis()
	{
		$redis_conf = $GLOBALS['config']['redis'];
		$redis = new Redis();
		$res = $redis->connect($redis_conf['host'],$redis_conf['port']);
		return $redis;
	}
}
