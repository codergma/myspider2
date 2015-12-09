<?php
require_once '/home/liubin/Downloads/myspider2/lib/CG_Redis.php';
/**
* Parse html contents
*/
class CG_Parse_Html
{
	/**
	* 筛选用户详细信息
	*
	* @param  string html信息
	* @return array
	*/
	public static function parse_user_info($content)
	{
	 	$data = array();

	    if (empty($content)) 
	    {
	        return NULL;
	    }
        //　用户名
        preg_match('#<meta name="apple-itunes-app"[\s\S]*app-argument=zhihu://people/([\s\S]*)">#U', $content,$out);
        $data['username'] = empty($out[1])?'':$out[1];
	    // 一句话介绍
	    preg_match('#<span class="bio" title=["|\'](.*?)["|\']>#', $content, $out);
	    $data['headline'] = empty($out[1]) ? '' : $out[1];

	    // 头像
	    //preg_match('#<img alt="龙威廉"\ssrc="(.*?)"\sclass="zm-profile-header-img zg-avatar-big zm-avatar-editor-preview"/>#', $content, $out);
	    preg_match('#<img class="avatar avatar-l" alt=".*?" src="(.*?)" srcset=".*?" />#', $content, $out);
	    $data['headimg'] = empty($out[1]) ? '' : $out[1];

	    // 居住地
	    preg_match('#<span class="location item" title=["|\'](.*?)["|\']>#', $content, $out);
	    $data['location'] = empty($out[1]) ? '' : $out[1];

	    // 所在行业
	    preg_match('#<span class="business item" title=["|\'](.*?)["|\']>#', $content, $out);
	    $data['business'] = empty($out[1]) ? '' : $out[1];

	    // 性别
	    preg_match('#<span class="item gender" ><i class="icon icon-profile-(.*?)"></i></span>#', $content, $out);
	    $gender = empty($out[1]) ? 'other' : $out[1];
	    if ($gender == 'female') 
	        $data['gender'] = 0;
	    elseif ($gender == 'male') 
	        $data['gender'] = 1;
	    else
	        $data['gender'] = 2;

	    // 公司或组织名称
	    preg_match('#<span class="employment item" title=["|\'](.*?)["|\']>#', $content, $out);
	    $data['employment'] = empty($out[1]) ? '' : $out[1];

	    // 职位
	    preg_match('#<span class="position item" title=["|\'](.*?)["|\']>#', $content, $out);
	    $data['position'] = empty($out[1]) ? '' : $out[1];

	    // 学校或教育机构名
	    preg_match('#<span class="education item" title=["|\'](.*?)["|\']>#', $content, $out);
	    $data['education'] = empty($out[1]) ? '' : $out[1];

	    // 专业方向
	    preg_match('#<span class="education-extra item" title=["|\'](.*?)["|\']>#', $content, $out);
	    $data['education_extra'] = empty($out[1]) ? '' : $out[1];

	    // 新浪微博
	    preg_match('#<a class="zm-profile-header-user-weibo" target="_blank" href="(.*?)"#', $content, $out);
	    $data['weibo'] = empty($out[1]) ? '' : $out[1];

	    // 个人简介
	    preg_match('#<span class="content">\s(.*?)\s</span>#s', $content, $out);
	    $data['description'] = empty($out[1]) ? '' : trim(strip_tags($out[1]));

	    // 关注了、关注者
	    preg_match('#<span class="zg-gray-normal">关注了</span><br />\s<strong>(.*?)</strong><label> 人</label>#', $content, $out);
	    $data['followees'] = empty($out[1]) ? 0 : intval($out[1]);
	    preg_match('#<span class="zg-gray-normal">关注者</span><br />\s<strong>(.*?)</strong><label> 人</label>#', $content, $out);
	    $data['followers'] = empty($out[1]) ? 0 : intval($out[1]);

	    // 关注专栏
	    preg_match('#<strong>(.*?) 个专栏</strong>#', $content, $out);
	    $data['followed'] = empty($out[1]) ? 0 : intval($out[1]);

	    // 关注话题
	    preg_match('#<strong>(.*?) 个话题</strong>#', $content, $out);
	    $data['topics'] = empty($out[1]) ? 0 : intval($out[1]);

	    // 关注专栏
	    preg_match('#个人主页被 <strong>(.*?)</strong> 人浏览#', $content, $out);
	    $data['pv'] = empty($out[1]) ? 0 : intval($out[1]);

	    // 提问、回答、专栏文章、收藏、公共编辑
	    preg_match('#提问\s<span class="num">(.*?)</span>#', $content, $out);
	    $data['asks'] = empty($out[1]) ? 0 : intval($out[1]);
	    preg_match('#回答\s<span class="num">(.*?)</span>#', $content, $out);
	    $data['answers'] = empty($out[1]) ? 0 : intval($out[1]);
	    preg_match('#专栏文章\s<span class="num">(.*?)</span>#', $content, $out);
	    $data['posts'] = empty($out[1]) ? 0 : intval($out[1]);
	    preg_match('#收藏\s<span class="num">(.*?)</span>#', $content, $out);
	    $data['collections'] = empty($out[1]) ? 0 : intval($out[1]);
	    preg_match('#公共编辑\s<span class="num">(.*?)</span>#', $content, $out);
	    $data['logs'] = empty($out[1]) ? 0 : intval($out[1]);

	    // 赞同、感谢、收藏、分享
	    preg_match('#<strong>(.*?)</strong> 赞同#', $content, $out);
	    $data['votes'] = empty($out[1]) ? 0 : intval($out[1]);
	    preg_match('#<strong>(.*?)</strong> 感谢#', $content, $out);
	    $data['thanks'] = empty($out[1]) ? 0 : intval($out[1]);
	    preg_match('#<strong>(.*?)</strong> 收藏#', $content, $out);
	    $data['favs'] = empty($out[1]) ? 0 : intval($out[1]);
	    preg_match('#<strong>(.*?)</strong> 分享#', $content, $out);
	    $data['shares'] = empty($out[1]) ? 0 : intval($out[1]);
	    return $data;
	}
	/**
	 * 筛选用户名
	 * 
	 * @param string 
	 * @param string $user_type followees 、followers
	 * @return void
	 */
	public static function parse_username($content,$user_type)
	{
	    if (empty($content)) 
	    {
	        return array();
	    }

	    $users = array();

	    // 用户不足20个的时候，从ajax取不到用户，所以首页这里还是要取一下
	    preg_match_all('#<h2 class="zm-list-content-title"><a data-tip=".*?" href="http://www.zhihu.com/people/(.*?)" class="zg-link" title=".*?">(.*?)</a></h2>#', $content, $out);
	    $count = count($out[1]);
	    for ($i = 0; $i < $count; $i++) 
	    {
	        $d_username = empty($out[1][$i]) ? '' : $out[1][$i]; 
	        $d_nickname = empty($out[2][$i]) ? '' : $out[2][$i]; 
	        if (!empty($d_username) && !empty($d_nickname)) 
	        {
	            $users[$d_username] = array(
	                'username'=>$d_username,
	                'nickname'=>$d_nickname,
	            );
	        }
	    }
/*
	    $keyword = $user_type == 'followees' ? '关注了' : '关注者';

	    preg_match('#<span class="zg-gray-normal">'.$keyword.'</span><br />\s<strong>(.*?)</strong><label> 人</label>#', $content, $out);
	    $user_count = empty($out[1]) ? 0 : intval($out[1]);

	    preg_match('#<input type="hidden" name="_xsrf" value="(.*?)"/>#', $content, $out);
	    $_xsrf = empty($out[1]) ? '' : trim($out[1]);

	    preg_match('#<div class="zh-general-list clearfix" data-init="(.*?)">#', $content, $out);
	    $url_params = empty($out[1]) ? '' : json_decode(html_entity_decode($out[1]), true);
	    if (!empty($_xsrf) && !empty($url_params) && is_array($url_params)) 
	    {
	        $url = "http://www.zhihu.com/node/" . $url_params['nodename'];
	        $params = $url_params['params'];

	        $j = 1;
	        for ($i = 0; $i < $user_count; $i=$i+20) 
	        {
	            $params['offset'] = $i;
	            $post_data = array(
	                'method'=>'next',
	                'params'=>json_encode($params),
	                '_xsrf'=>$_xsrf,
	            );
	            $content = cls_curl::post($url, $post_data);
	           
	            $rows = json_decode($content, true);

	            foreach ($rows['msg'] as $row) 
	            {
	                preg_match_all('#<h2 class="zm-list-content-title"><a data-tip=".*?" href="http://www.zhihu.com/people/(.*?)" class="zg-link" title=".*?">(.*?)</a></h2>#', $row, $out);
	                $d_username = empty($out[1][0]) ? '' : $out[1][0]; 
	                $d_nickname = empty($out[2][0]) ? '' : $out[2][0]; 
	                if (!empty($d_username) && !empty($d_nickname)) 
	                {
	                    $users[$d_username] = array(
	                        'username'=>$d_username,
	                        'nickname'=>$d_nickname,
	                    );
	                }
	            }
	            $j++;
	        }
	    }
*/
	    return $users;
	}

}
