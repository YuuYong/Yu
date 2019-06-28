<?php
/**
 * Created by PhpStorm.
 * User: YuYong
 * Date: 2019/6/28
 * Time: 14:17
 */

namespace App\controller;

use sdk\libs\MysqlHelper;
use Katzgrau\KLogger\Logger;

class TestController
{
    public function test(){



        $mysql = MysqlHelper::getInstance();
        $re = $mysql->getOne('select * from test.order_2');
        $run_time = run_time();
        var_dump($re);

    }
}