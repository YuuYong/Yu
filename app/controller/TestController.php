<?php

namespace App\controller;

use sdk\libs\MysqlHelper;
use sdk\libs\HttpHelper;
use Katzgrau\KLogger\Logger;

class TestController extends ApiBaseController
{
    public function test(){

        return $this->view('aaa/index',['the' => 'variables', 'go' => 'here']);

    }
}