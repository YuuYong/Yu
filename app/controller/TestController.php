<?php

namespace App\controller;

use sdk\libs\MysqlHelper;
use sdk\libs\RedisHelper;
use sdk\libs\HttpHelper;
use Katzgrau\KLogger\Logger;

class TestController extends ApiBaseController
{

    //视图引用示例
    public function html(){

        return $this->view('aaa/index',['the' => 'variables', 'go' => 'here']);

    }


    //mysql引用示例
    public function mysql(){
        $mysql = MysqlHelper::getInstance();
        $sql = "select * from test.area where pid = :pid";
        $var = [
            ':pid' => 42
        ];
        $re = $mysql->getAll($sql,$var);
        return $re;
    }


    //redis引用示例
    public function redis(){

        $redis = RedisHelper::connect('s1');
        $re = $redis->get('cm_retail:setting:keys');
        return $re;
    }


    //http请求引用示例
    public function http(){
        $url = "http://api.ip138.com/query/?ip=8.8.8.8&datatype=jsonp";
        $result = HttpHelper::get($url);
        return $result;
    }


    //jsonp请求引用示例
    public function info(){
        $callback = $_GET['callback'];
        $info = [
            'name' => '张三',
            'age' => 18
        ];
        return $this->jsonp($info,$callback);
    }


    /**
     * 文件下载请求引用示例
     */
    public function files(){
        //$file = PUBLIC_PATH.'/static/video/8.6_0.mp4';
        $file = PUBLIC_PATH.'/static/video/showappvideo.mp4';
        $this->file($file,'showappvideo');
    }


    //视频流请求引用示例
    public function video(){
        //$file = PUBLIC_PATH.'/static/video/8.6_0.mp4';
        $file = PUBLIC_PATH.'/static/video/showappvideo.mp4';
        $this->mp4($file);
    }


}