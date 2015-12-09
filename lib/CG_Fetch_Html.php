<?php
require_once '/home/liubin/Downloads/myspider2/third_lib/rolling_curl/RollingCurl.php';

/**
* 抓取网页内容类 
*/
class CG_Fetch_Html 
{
	/**
	* @var string 
	*/
	private $cookie;
	/**
	* @var string
	*/
	private $useragent;
	/**
	* @var array  
	*/
	private $options = array();
	/**
	* @var array
	*/
	private $urls;
	/**
	* @var int curl批处理句柄最大同时连接数
	*/
	private $window_size = 5;
	/**
	* @var string 回调函数
	*/
	private $callback;

	/**
	* 构造函数
	* 
	* @param array $urls 要抓取的url
	* @param array $callback 回到函数
	*/
	public function __construct($urls,$callback)
	{
		$this->urls        = $urls;
		$this->callback    = $callback;
	}
	public function __set($name,$value)
	{
		switch ($name)
		{
			case 'cookie':
				$this->options[CURLOPT_COOKIE] = $value;
				break;
			case 'useragent':
				$this->options[CURLOPT_USERAGENT] = $value;
				break;
			case 'options':
	            $this->options = $this->options + $value;
	            break;
			default:
				break;
		}
	}
	
	/**
	* 抓取网页
	*
	* @param int $window_size curl批处理句柄最大同时连接数
	*/
	public function fetch_html($window_size=NULL)
	{
		$rc = new RollingCurl($this->callback);
		foreach ($this->urls as $url)
		{
			$request = new RollingCurlRequest($url,'Get',NULL,NULL,$this->options);
			$rc->add($request);	
		}
		if (!empty($window_size))
		{
			$rc->execute($window_size);
		}
		else
		{
			$rc->execute($this->window_size);
		}
	}
}
