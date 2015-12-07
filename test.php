<?php
require_once 'lib/CG_Parse.php';
require_once 'lib/CG_User_Info.php';
define('COUNT', 60);

// 回调函数，用来处理抓取到的用户详情页面信息
function info_callback($response)
{
	if (!empty($response))
	{
		$info = CG_Parse_Html::parse_user_info($response);

		if(!empty($info)
		{
			CG_User_Info::sav_user_info($info);
		}
	}
}
// 回调函数，用来处理关注者和挂住了页面信息
function followees_callback($response)
{
	if (!empty($response))
	{
		$info = CG_Parse_Html::parse_username($response,'followees');

		if(!empty($info)
		{
			CG_User_Info::save_username($info);
		}
	}
}
function followers_callback($response)
{
	if (!empty($response))
	{
		$info = CG_Parse_Html::parse_username($response,'followers');

		if(!empty($info)
		{
			CG_User_Info::save_username($info);
		}
	}
}

$cookie_path = 'config/cookie.php';
$useragent   = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';
$callback    = array(
	'info'=>'info_callback',
	'followees'=>'followees_callback',
	'followers'=>'followers_callback');

// MySQL为起点从中抓取还没有便利关注者和关注了的用户
$users = CG_User_Info::get_users();
// 抓取关注了的用户并保存到redis
$urls = array();
foreach ($users as $user)
{
	$urls[] = "http://www.zhihu.com/people/{$user}/followees";
}
$fetch_followees   =  new CG_Fetch_Html($urls,$callback['followees'],$cookie_path,$useragent);
$fetch_followees->fetch_html();
// 抓取关注者的用户并保存到redis
$urls = array();
foreach ($users as $user)
{
	$urls[] = "http://www.zhihu.com/people/{$user}/followers";
}
$fetch_followers   =  new CG_Fetch_Html($urls,$callback['followers'],$cookie_path,$useragent);
$fetch_followers->fetch_html();

// 获取用户
$usernames = get_username(COUNT);

//　抓取用户信息,并调用回调函数处理
