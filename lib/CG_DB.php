<?php
require_once '/home/liubin/Downloads/myspider2/config/config.php';
/**
* 数据库类
*
* @author codergma<codergma@163.com>
* @link http://blog.codergma.com
*/
class CG_DB
{
	/**
	* @var MySQLi $db 数据库对象
	*/
	private static $db;
	/**
	* @var mysqli_result $mysqli_rst mysqli_result对象
	*/
	private static $mysqli_rst;

	/**
	* 初始化数据库
	* 
	* @param array $config 数据库配置
	*/
	protected static function _init_mysql($config=array())
	{
		if ($config == NULL)
		{
			$config = $GLOBALS['config']['db'];
		}
		if (!self::$db)
		{
			self::$db = new mysqli($config['host'],$config['user'],$config['password'],$config['db_name']);
			if (0 == self::$db->connect_errno)
			{
				self::$db->set_charset('utf8');
			}
			else
			{
				echo 'MySQL connect error'.PHP_EOL;
			}
		}
	}
	/**
	* 数据库查询函数
	*
	* @param string $sql 查询语句 
	* @return mixed
	*/
	public static function query($sql)
	{
		$sql = trim($sql);
		if(self::$db == NULL)
		{
			self::_init_mysql();
		}

		self::$mysqli_rst = self::$db->query($sql);
		if (FALSE == self::$mysqli_rst)
		{
			echo 'MySQL query  errno : '.self::$db->errno.PHP_EOL;
			$backtrace = debug_backtrace();
			var_dump($backtrace);
			return FALSE;
		}
		else
		{
			return self::$mysqli_rst;
		}
	}
}
