<?php
require_once '/home/liubin/Downloads/myspider2/config/config.php';
/**
* 数据库类
*
* @author codergma<codergma@163.com>
*/
class CG_DB
{
	private static $db;
	private static $rsid;
	private static $query_count;

	/**
	* _init_mysql
	* 初始化数据库
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
			if (0 !== self::$db->connect_errno)
			{
				echo 'MySQL connect error'.PHP_EOL;
			}
			else
			{
				self::$db->set_charset('utf8');
			}
		}
	}
	/**
	* query
	* 数据库查询函数
	*
	* @param string 
	*/
	public static function query($sql)
	{
		$sql = trim($sql);
		if(self::$db == NULL)
		{
			self::_init_mysql();
		}

		self::$rsid = self::$db->query($sql);
		if (FALSE == self::$rsid)
		{

			echo 'MySQL query  errno : '.self::$db->errno;
			echo '<br/>';
			$backtrace = debug_backtrace();
			var_dump($backtrace);
            // array_shift($backtrace);
            // $narr = array('class', 'type', 'function', 'file', 'line');
            // $err = "debug_backtrace：\n";
            // foreach($backtrace as $i => $l)
            // {
            //     foreach($narr as $k)
            //     {
            //         if( !isset($l[$k]) ) $l[$k] = '';
            //     }
            //     $err .= "[$i] in function {$l['class']}{$l['type']}{$l['function']} ";
            //     if($l['file']) $err .= " in {$l['file']} ";
            //     if($l['line']) $err .= " on line {$l['line']} ";
            //     $err .= "\n\n";
            // }
            // echo $err;
			return FALSE;
		}
		else
		{
			self::$query_count++;
			return self::$rsid;
		}
	}
}
