<?php

namespace App\controller;

use sdk\libs\MysqlHelper;
use Katzgrau\KLogger\Logger;

class TestController extends ApiBaseController
{
    public function test(){



        $mysql = MysqlHelper::getInstance();
        $re = $mysql->getOne('select * from test.order_2');
        $run_time = run_time();

        return $this->success('',$run_time);


    }
}