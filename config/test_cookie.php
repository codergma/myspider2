<?php
$url = "http://www.zhihu.com/people/lin-taro";
$cookie = file_get_contents('cookie.txt');
$useragent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_COOKIE,$cookie);
curl_setopt($ch, CURLOPT_USERAGENT,$useragent);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
$content = curl_exec($ch);
file_put_contents('/home/liubin/Desktop/lin.html', $content);