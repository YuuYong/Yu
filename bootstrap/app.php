<?php

namespace bootstrap;

use Katzgrau\KLogger\Logger;

class App
{
    public static function run(){

        //1.首先定义目录常量
        self::defDir();

        //2.其次定义公共函数
        self::defFunc();

        //3.路由解析
        self::getRouter();
    }


    /**
     * 引入路径常量
     * 使用 DIRECTORY_SEPARATOR 自动获取当前系统的路径分隔符
     */
    private static function defDir(){

        define('VERSION','1.0.0');//版本

        define('PJ_PATH',dirname(__DIR__).DIRECTORY_SEPARATOR);//项目根目录

        define('APP_PATH',PJ_PATH.'app'.DIRECTORY_SEPARATOR);//项目主目录

        define('CONFIG_PATH',PJ_PATH.'config'.DIRECTORY_SEPARATOR);//项目配置文件目录

        define('CACHE_PATH',PJ_PATH.'cache'.DIRECTORY_SEPARATOR);//项目缓存目录

        define('LOG_PATH',PJ_PATH.'logs'.DIRECTORY_SEPARATOR);//项目日志目录

        define('VENDOR_PATH',PJ_PATH.'sdk'.DIRECTORY_SEPARATOR);//项目扩展包目录

        require PJ_PATH.'app/helps/constants.php';//引入预定义常量

    }


    /**
     * 引入公共函数
     */
    private static function defFunc(){

        require PJ_PATH.'app/helps/functions/public_function.php';

    }


    private static function getRouter(){
        $route = require PJ_PATH . 'router/api.php';
        //http://yu.com/api-v4/user?id=21554&name=yu.you#1025
        $url = 'http://';
        if(isset($_SERVER['HTTPS'])){
            $url = 'https://';
        }
        $url .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $path_arr = parse_url($url);
        $request_path = trim($path_arr['path'],'/');
        $router_arr = explode('/',$request_path);
        //这里 count($router_arr) 一定大于0，不信自己试
        if(count($router_arr) > 0){
            try{
                //检查路由(逐级检查)
                for($i=0;$i<count($router_arr);$i++){
                    if(!isset($route[$router_arr[$i]])){
                        throw new \Exception('Router Not Found!');//404
                    }else{
                        $route = $route[$router_arr[$i]];
                    }
                }

                //取控制器+方法
                //var_dump($route);die;
                if(!is_string($route)){
                    throw new \Exception('Router Not Found!',404);//404
                }
                $route_arr = explode('@',$route);
                $controller = $route_arr[0] ?? '';
                $method = $route_arr[1] ?? '';
                if(empty($controller)){
                    throw new \Exception("Controller Set Error In Router!");
                }
                if(empty($method)){
                    throw new \Exception("Method Set Error In Router!");
                }
                $class_name = 'App\controller\\'.$controller;
                $class = new $class_name();
                if(!method_exists($class,$method)){
                    throw new \Exception("Method Not Found!");
                }
                $response = $class->$method();
            }catch(\PDOException $e){
                $response = get_exception($e);
                $logger = new Logger(LOG_PATH.'app');
                $logger->error($response);
            }catch(\Exception $e){
                $response = get_exception($e);
                $logger = new Logger(LOG_PATH.'app');
                $logger->error($response);
            }catch(\Error $e){
                $response = get_exception($e);
                $logger = new Logger(LOG_PATH.'app');
                $logger->error($response);
            }
            if(!is_null($response)){
                if(is_array($response)){
                    echo json_encode($response,256);
                }elseif(is_string($response)){
                    echo $response;
                }elseif(is_object($response)){
                    echo $response;
                }else{
                    echo $response;
                }
            }
            //执行终止
        }else{
            echo '不可能到这里来';
        }
    }
}