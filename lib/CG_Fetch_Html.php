<?php
require_once '../third_lib/rolling_curl/RollingCurl.php';

/**
 * 回调函数
 */
 function _request_callback($response)
{
    echo 'abc';
    $filename = uniqid().'.html';
    $rst = file_put_contents($filename, $response);
    return $rst>0 ? TRUE:FALSE;
}
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
	* 构造函数
	* $cookie_path string
	* $useragent string
	* $options   array
	* $urls      array
	* $window_size int
	*/
	public function __construct($urls,$cookie_path=NULL,$useragent=NULL,$options=array(),$window_size=5)
	{
		$this->cookie_path = $cookie_path;
		$this->useragent   = $useragent; 
		$this->options     = $options;
		$this->urls        = $urls;
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
			$this->options = array_merge($this->options,$options);
		}
	}
	public function fetch_html()
	{
		$rc = new RollingCurl('_request_callback');
		foreach ($this->urls as $url)
		{
			$request = new RollingCurlRequest($url,'Get',NULL,NULL,$this->options);
			$rc->add($request);	
		}
		$rc->execute($this->window_size);
	}


}
	//回调函数，保存内容到文件
	// function request_callback($response)
	// {
	// 	$filename = uniqid().'.html';
	// 	$rst = file_put_contents($filename, $response);
	// 	return $rst>0 ? TRUE:FALSE;
	// }

	// $cookie = file_get_contents('cookie.txt');
	// $useragent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';
	// $option = array(
	// 	CURLOPT_COOKIE=>$cookie,
	// 	CURLOPT_USERAGENT=>$useragent);
	// $urls   = array(
	// 	'http://www.zhihu.com/people/lin-taro',
	// 	'http://www.zhihu.com/people/codergma');

	// $rc  = new RollingCurl('request_callback');
	// foreach ($urls as $url)
	// {
	// 	$request = new RollingCurlRequest($url,'Get',NULL,NULL,$option);
	// 	$rc->add($request);
	// }
	// $rc->execute();

	// }