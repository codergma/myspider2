<?php
require_once '/home/liubin/Downloads/myspider2/third_lib/rolling_curl/RollingCurl.php';

/**
* 抓取网页内容 
*/
class CG_Fetch_Html 
{
	/**
	* @var string
	*/
	private $cookie_path;
	/**
	* @var string
	*/
	private $useragent;
	/**
	* @var array 
	*/
	private $options;
	/**
	* @var array
	*/
	private $urls;
	/**
	* @var int
	*/
	private $window_size;
	/**
	* @var string
	*/
	private $callback;

	/**
	* 构造函数
	* $cookie_path string
	* $useragent string
	* $options   array
	* $urls      array
	* $window_size int
	*/
	public function __construct($urls,$callback,$cookie_path=NULL,$useragent=NULL,$options=array(),$window_size=2)
	{
		$this->urls        = $urls;
		$this->callback    = $callback;
		$this->cookie_path = $cookie_path;
		$this->useragent   = $useragent; 
		$this->options     = $options;
		$this->window_size = $window_size;
		$this->_init();
	}
	private function _init()
	{
		$options = array();
		if (!empty($this->cookie_path))
		{
			$cookie  = file_get_contents($this->cookie_path);
			$options[CURLOPT_COOKIE] = $cookie;
		}
		if (!empty($this->useragent))
		{
			$options[CURLOPT_USERAGENT] = $this->useragent;
		}
		if (!empty($options))
		{
            $this->options = $this->options + $options;
		}
	}
	public function fetch_html()
	{
		$rc = new RollingCurl($this->callback);
		foreach ($this->urls as $url)
		{
			$request = new RollingCurlRequest($url,'Get',NULL,NULL,$this->options);
			$rc->add($request);	
		}
		$rc->execute($this->window_size);
	}
}
