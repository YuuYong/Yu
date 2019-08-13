<?php

namespace App\traits;

use Twig\Error\Error;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use \Twig\Loader\FilesystemLoader as TwigLoad;
use \Twig\Environment as TwigConfig;
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

    /**
     * 视图输出
     * @param $view_path
     * @param $param
     * @return string
     */
    public function view($view_path,$param = [])
    {
        try{
            $loader = new TwigLoad(VIEW_PATH);
            $twig = new TwigConfig($loader, [
                'cache' => CACHE_PATH.'templates',
                'debug' => true,
                'auto_reload' => true,
            ]);
            if(!empty($view_path)){
                $view_path .= '.html';
            }
            if(empty($param)){
                $template = $twig->load($view_path);
            }else{
                $template = $twig->render($view_path,$param);
            }
            return $template;
        }catch(LoaderError $e){
            exit('TwigLoaderError');
        }catch (RuntimeError $e){
            exit('TwigRuntimeError');
        }catch(SyntaxError $e){
            exit('TwigSyntaxError');
        }
    }

}