<?php

namespace App\traits;

/**
 * 模块化复用
 *
 * Trait ResponseTrait
 * @package App\traits
 */
trait ResponseTrait
{
    /**
     * 接口调用成功返回数据格式
     * @param string $msg
     * @param array $data
     * @return array
     */
    public function success($msg = '', $data = [])
    {
        return [
            'ret' => 'success',
            'code' => 0,
            'msg' => $msg,
            'data' => $data
        ];
    }

    /**
     * 接口调用失败返回数据格式
     * @param $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public function error($code, $msg = '', $data = [])
    {
        return [
            'ret' => 'fail',
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
    }

    /**
     * 文件流输出
     * @param $filePath
     * @param $title
     */
    public function file($filePath, $title)
    {
        $file = fopen($filePath, "rb");
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Content-Disposition: attachment; filename= $title");
        while (!feof($file)) {
            echo fread($file, 8192);
            ob_flush();
            flush();
        }
        fclose($file);
    }

}