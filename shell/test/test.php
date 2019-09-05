<?php
define('APP_RUN_START_TIME',microtime(true));

require __DIR__.'/../../vendor/autoload.php';

require __DIR__.'/../../bootstrap/app.php';

bootstrap\App::run_cli();

/**
 * 以下为脚本内容
 */

use sdk\libs\HttpHelper;

$url = "https://www.jd.com";
$headers = [
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.87 Safari/537.36',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
    'Accept-Language: zh-CN,zh;q=0.9',
    //'Cookie: gaDts48g=q8h5pp9t; tcc; aby=2; ppu_main_9ef78edf998c4df1e1636c9a474d9f47=1; expla=2',
];
$result =  HttpHelper::get($url,$headers,60);

var_dump($result);