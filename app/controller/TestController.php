<?php

namespace App\controller;

use sdk\libs\MysqlHelper;
use sdk\libs\HttpHelper;
use Katzgrau\KLogger\Logger;

class TestController extends ApiBaseController
{
    public function test(){


        $url = "http://api.ip138.com/query/?ip=202.103.24.68&datatype=json";
        $data = HttpHelper::get($url);
        return $data;
        /*$mysql = MysqlHelper::getInstance();
        $re = $mysql->getOne('select * from test.order_2');
        $run_time = run_time();*/

        /*$file = PUBLIC_PATH.'Yu.zip';
        $this->file($file,'项目压缩包.zip');*/


    }
}