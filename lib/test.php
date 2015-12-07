<?php
require_once 'CG_Fetch_Html.php';

$cookie = '../config/cookie.txt';
$useragent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';
$urls   = array(
	'http://www.zhihu.com/people/lin-taro',
	'http://www.zhihu.com/people/codergma');
$fetch = new CG_Fetch_Html($urls,$cookie,$useragent);
$fetch->fetch_html();

