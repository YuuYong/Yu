<?php

namespace sdk\libs;

use Katzgrau\KLogger\Logger;

/**
 * Class HttpHelper
 * @package sdk\libs
 */
class HttpHelper
{

    private function __construct()
    {

    }


    /**
     * @param $url
     * @param $postdata
     * @param array $header
     * @param int $timeout
     * @param array $cert
     * @return mixed
     */
    public static function post($url, $postdata, $header = array(), $timeout = 5, $cert = array())
    {
        if (!function_exists('curl_init')) {
            $logger = new Logger(LOG_PATH . "http");
            $logger->critical("server not install curl\n");
        }
        $header[] = 'Expect:';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $url_array = parse_url($url);
        if ($url_array ['scheme'] == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
            if (isset($cert['cert'])) {
                curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
                curl_setopt($ch, CURLOPT_SSLCERT, $cert['cert']);
            }
            if (isset($cert['key'])) {
                curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
                curl_setopt($ch, CURLOPT_SSLKEY, $cert['key']);
            }
            if (isset($cert['pass'])) {
                curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $cert['pass']);
            }
        }

        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $data = curl_exec($ch);

        $arrtemp = explode("\r\n\r\n", $data);
        if (count($arrtemp) < 2) {
            curl_close($ch);
            return '';
        }

        list($header, $data) = explode("\r\n\r\n", $data);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 301 || $http_code == 302) {
            $url = self::getLocationUrl($header, $url_array, $url);

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);

            $data = curl_exec($ch);
        }

        if ($http_code >= 400) {
            $log_path = LOG_PATH . "http";
            $logger = new Logger($log_path);
            $logger->critical("{$url}," . json_encode($postdata) . ",http_code:{$http_code}");
        }

        if ($data == false) {
            $m_error = curl_error($ch);
            curl_close($ch);
            $log_path = LOG_PATH . "http";
            $logger = new Logger($log_path);
            $logger->critical("url:{$url},data:" . json_encode($postdata) . "\nerror_msg:{$m_error}\n");
        }
        @curl_close($ch);
        return $data;
    }


    /**
     * 发送get请求
     * @param $url
     * @param array $header
     * @param int $timeout
     * @return mixed
     */
    public static function get($url, $header = array(), $timeout = 5)
    {
        if (!function_exists('curl_init')) {
            $logger = new Logger(LOG_PATH . "http");
            $logger->critical("server not install curl\n");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $url_array = parse_url($url);
        if ($url_array ['scheme'] == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        }

        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $data = curl_exec($ch);
        if ($data == false) {
            $m_error = curl_error($ch);
            $logger = new Logger(LOG_PATH . "http");
            $logger->critical("{$url},error_msg:{$m_error}\n");
        }
        $arrtemp = explode("\r\n\r\n", $data);
        if (count($arrtemp) < 2) {
            curl_close($ch);
            return '';
        }

        list($header, $data) = explode("\r\n\r\n", $data);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 301 || $http_code == 302) {
            $url = self::getLocationUrl($header, $url_array, $url);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $data = curl_exec($ch);
        }

        if ($http_code >= 400) {
            $logger = new Logger(LOG_PATH . "http");
            $logger->critical("{$url}," . ",http_code:{$http_code}");
        }

        if ($data == false) {
            $m_error = curl_error($ch);
            curl_close($ch);
            $logger = new Logger(LOG_PATH . 'http');
            $logger->critical("{$url},error_msg:{$m_error}\n");
        }
        @curl_close($ch);
        return $data;
    }


    /**
     * @param $header
     * @param $url_array
     * @param $req_url
     * @return mixed|string
     */
    public static function getLocationUrl($header, $url_array, $req_url)
    {
        $header_lines = explode("\r\n", $header);
        $header_kv = array();
        foreach ($header_lines as $line) {
            $tmp = explode(": ", $line);
            if (count($tmp) == 2) {
                $header_kv[strtolower($tmp[0])] = $tmp[1];
            }
        }

        $url = isset($header_kv["location"]) ? $header_kv["location"] : "";
        if (!preg_match("/{$url_array["host"]}/", $url)) {
            if (substr($url, 0, 1) == "/") {
                $url = "{$url_array['scheme']}://{$url_array["host"]}{$url}";
            } else {
                $tmp_arr = explode("/", $req_url);
                $tmp_arr[count($tmp_arr) - 1] = $url;
                $url = implode("/", $tmp_arr);
            }
        }

        return $url;
    }

}

?>