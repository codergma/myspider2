<?php
require_once '/home/liubin/Downloads/myspider2/lib/CG_Parse_Html.php';
require_once '/home/liubin/Downloads/myspider2/lib/CG_User_Info.php';
define('COUNT', 60);

// 回调函数，用来处理抓取到的用户详情页面信息
function info_callback($response)
{
	if (!empty($response))
	{
		$info = CG_Parse_Html::parse_user_info($response);

		if(!empty($info))
		{
			CG_User_Info::save_user_info($info);
		}
	}
}


$cookie_path = '/home/liubin/Downloads/myspider2/config/cookie.txt';
$cookie  = file_get_contents($cookie_path);
$useragent   = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';
$callback    = array('info'=>'info_callback');
$options = array(
	CURLOPT_RETURNTRANSFER=>TRUE,
	CURLOPT_FOLLOWLOCATION=>TRUE);

while(1)
{

	$cg_user_info = new CG_User_Info();
	$users = $cg_user_info->get_redis_user();
	$urls = array();
	foreach ($users as $user)
	{
		$urls[] = "http://www.zhihu.com/people/{$user}/about";
	}

	$fetch   =  new CG_Fetch_Html($urls,$callback['info']);
	$fetch->cookie = $cookie;
	$fetch->useragent = $useragent;
	$fetch->options = $options;
	$fetch->fetch_html();
}
