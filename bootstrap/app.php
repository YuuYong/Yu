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

        //3.Header设置
        self::setHeader();

        //4.路由解析
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

        /**
         * 更多公共函数添加在下方
         */



    }


    /**
     * Header设置
     */
    private static function setHeader(){

        //X-Powered-By
        header("X-Powered-By: Yu-Framework");

        //响应格式
        header('Content-Type: application/json;charset=utf-8');

    }


    /**
     * 加载路由
     */
    private static function getRouter(){
        //http://yu.com/api-v4/user?id=21554&name=yu.you#1025
        $url = 'http://';
        if(isset($_SERVER['HTTPS'])){
            $url = 'https://';
        }
        $url .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $path_arr = parse_url($url);
        $request_path = trim($path_arr['path'],'/');
        try{
            //检查路由
            $route = require PJ_PATH . 'router/api.php';

            //全路由查询(方案一)
            $route_cache_folder = CACHE_PATH.'router';
            create_folders($route_cache_folder);
            $route_cache_file = $route_cache_folder.'/all_route.txt';
            if(file_exists($route_cache_file) && time() - filemtime($route_cache_file) < 3600){
                $all_route = file_get_contents($route_cache_file);
                $all_route = json_decode($all_route,true);
            }else{
                $all_route = get_all_route('',$route);
                file_put_contents($route_cache_file,json_encode($all_route));
            }
            if(isset($all_route[$request_path])){
                $route = $all_route[$request_path];
            }else{
                throw new \Exception('Router Not Found!');//404
            }

            //逐级检查(方案二)
            /*$router_arr = explode('/',$request_path);
            for($i=0;$i<count($router_arr);$i++){
                if(!isset($route[$router_arr[$i]])){
                    throw new \Exception('Router Not Found!');//404
                }else{
                    $route = $route[$router_arr[$i]];
                }
            }*/

            //取控制器+方法
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
    }
}