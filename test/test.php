<?php
require_once '../third_lib/rolling_curl/RollingCurl.php';

//回调函数，保存内容到文件
function request_callback($response)
{
	$filename = uniqid().'.html';
	$rst = file_put_contents($filename, $response);
	return $rst>0 ? TRUE:FALSE;
}

$cookie = file_get_contents('cookie.txt');
$useragent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';
$option = array(
	CURLOPT_COOKIE=>$cookie,
	CURLOPT_USERAGENT=>$useragent);
$urls   = array(
	'http://www.zhihu.com/people/lin-taro',
	'http://www.zhihu.com/people/codergma');

$rc  = new RollingCurl('request_callback');
foreach ($urls as $url)
{
	$request = new RollingCurlRequest($url,'Get',NULL,NULL,$option);
	$rc->add($request);
}
$rc->execute();
