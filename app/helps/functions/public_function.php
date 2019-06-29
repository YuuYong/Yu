<?php

/**
 * 配置读取
 * @param $key
 * @param string $default
 * @return string
 */
function env($key,$default = ''){
    $env = parse_ini_file(PJ_PATH.'.env');
    if(isset($env[$key])){
        return $env[$key];
    }else{
        return $default;
    }
}

/**
 * @param $data
 * @param bool $exit
 */
function dump($data, $exit = false)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    if ($exit) exit;
}


/**
 * 获取程序执行时间
 * @return int|mixed
 */
function run_time(){
    if(defined('APP_RUN_START_TIME')){
        return microtime(true) - APP_RUN_START_TIME;
    }else{
        return 0;
    }
}


/**
 * @param Exception $e
 * @param string $type
 * @param array $params
 * @return string
 */
function get_exception(\Exception $e, $type = '',$params = []){
    switch($type){
        case 'mysql':
            return $e->getMessage();
            break;

        case 'sql':
            $content = $e->getMessage().PHP_EOL;
            $content .= isset($params['sql']) ? 'error_sql:'.$params['sql'] : '';
            return $content;
            break;

        default:
            return $e->getMessage();
    }
}


/**
 * 获取全部路由
 * @param $key
 * @param $route
 * @return array
 */
function get_all_route($key, $route){
    static $route_list = [];
    if(is_array($route)){
        foreach($route as $v=>$k){
            $temp_route = trim($key,'/').'/'.trim($v,'/');
            $temp_route = trim($temp_route,'/');
            get_all_route($temp_route,$k);
        }
    }else{
        $route_list[$key] = $route;
    }
    return $route_list;
}


/**
 * @param $dir
 * @return bool
 */
function create_folders($dir)
{
    return is_dir($dir) or (create_folders(dirname($dir)) and mkdir($dir, 0777));
}


/**
 * @param $url
 * @param array $data
 * @return mixed
 */
function http_request($url, $data = array())
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    // POST数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // 把post的变量加上
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}