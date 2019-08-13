<?php

namespace sdk\libs;


class ConfigHelper
{

    public static function mysql()
    {
        //这里如果使用 __METHOD__ 可能含有命名空间：vendor\libs\ConfigHelper::mysql
        return self::getConfig(__FUNCTION__);
    }


    public static function mysql_slave()
    {
        return self::getConfig(__FUNCTION__);
    }


    public static function redis()
    {
        return self::getConfig(__FUNCTION__);
    }


    private static function getConfig($config_name)
    {
        //读取自定义配置
        $env_file = PJ_PATH . '.env';
        if (file_exists($env_file)) {
            $env_data = parse_ini_file(PJ_PATH . '.env', true);
            $config = isset($env_data[$config_name]) ? $env_data[$config_name] : null;
            if ($config) {
                return $config;
            }
        }

        //读取默认配置
        $config_file = CONFIG_PATH . $config_name . '.php';
        if (file_exists($config_file)) {
            $config_data = require $config_file;
            return $config_data;
        }

        return null;
    }
}