<?php
require_once 'CG_Fetch_Html.php';


/**
 * 回调函数
 * @param string 抓取的html内容
 */
 function request_callback($response)
{
    $filename = uniqid().'.html';
    $rst = file_put_contents($filename, $response);
    return $rst>0 ? TRUE:FALSE;
}

$callback    = 'request_callback';
$cookie_path = '../config/cookie.txt';
$useragent   = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';
$urls        = array(
			'http://www.zhihu.com/people/lin-taro',
			'http://www.zhihu.com/people/codergma');

$fetch   =  new CG_Fetch_Html($urls,'request_callback',$cookie_path,$useragent);
$fetch->fetch_html();



