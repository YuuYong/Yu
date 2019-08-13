<?php

namespace sdk\libs;

class RedisHelper {

    private static $static_redis = [];//静态redis实例数组

    private static $last_connect_time = [];//最后连接时间

    private static $timeout = 5;//连接超时时间


    /**
     * 连接指定redis实例
     * @param $s
     * @return mixed|\redis
     */
    public static function connect($s) {
        $now_time = time();
        if (isset(self::$static_redis[$s]) && ($now_time - self::$last_connect_time[$s] < 60)) {
            self::$last_connect_time[$s] = $now_time;
            return self::$static_redis[$s];

        } else {
            self::$last_connect_time[$s] = $now_time;
            $static_redis_c = new \redis();
            $redis_config = ConfigHelper::redis();
            $static_redis_c->pconnect($redis_config['REDIS_HOST'], $redis_config['REDIS_PORT'], self::$timeout);
            //auth
            if(!empty($redis_config['REDIS_PASSWORD'])){
                $static_redis_c->auth($redis_config['REDIS_PASSWORD']);
            }
            self::$static_redis[$s] = $static_redis_c;
            return $static_redis_c;
        }
    }


}
