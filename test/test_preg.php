<?php
$data = array();
$content = file_get_contents('/home/liubin/Desktop/lin.html');
preg_match('#<meta name="apple-itunes-app"[\s\S]*app-argument=zhihu://people/([\s\S]*)">#U', $content,$out);
var_dump($out[1]);
