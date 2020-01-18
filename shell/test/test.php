<?php
define('APP_RUN_START_TIME',microtime(true));

require __DIR__.'/../../vendor/autoload.php';

require __DIR__.'/../../bootstrap/app.php';

bootstrap\App::run_cli();

/**
 * 以下为脚本内容
 */

use sdk\libs\HttpHelper;

//清除换行、空格、制表符。避免干扰
function clear($contents){
    $contents = str_replace("\r\n", '', $contents); //清除换行符
    $contents = str_replace("\n", '', $contents); //清除换行符
    $contents = str_replace("\t", '', $contents); //清除制表符
    $contents = str_replace(" ", '', $contents); //清除空格符
    return $contents;
}

//下载图片
function downloadImage($url, $path = './images/') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);// 要访问的地址
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// 获取的信息以文件流的形式返回
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);// 成功连接服务器前的等待时间，应对服务器过载（连接时间）
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);// 成功连接服务器后的最大文件输出时间（下载时间）
    $file = curl_exec($ch);
    curl_close($ch);
    return saveAsImage($url, $file, $path);
}

//保存图片
function saveAsImage($url, $file, $path) {
    $filename = pathinfo($url, PATHINFO_BASENAME);
    $filename = md5($filename) . substr($filename, strpos($filename, '.'));
    $resource = fopen($path . $filename, 'wb');
    is_dir($path) or (create_folders(dirname($path)) and mkdir($path, 0777));
    fwrite($resource, $file);
    fclose($resource);
    return $filename;
}


$url = "https://detail.tmall.com/item.htm?id=591672684898&skuId=4239716298626";
$headers = [
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36',
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
    'accept-language: zh-CN,zh;q=0.9',
    'accept-encoding: gzip, deflate, br',
    'cookie: cna=vAYmFb9+TTACAXF0WA+qV8wH; cq=ccp%3D1; t=b4fa727fa7119c37a6227dc0d97a2286; _tb_token_=74936e87e5451; cookie2=1b317211c282a83af3dde496fa7ed328; pnm_cku822=098%23E1hvIvvUvbpvUpCkvvvvvjiPRFLZtjEjPFzZljljPmPWlj1Un2LpAjnVPLMZ1jrbR2yCvvpvvvvvvphvC9vhvvCvpvyCvhQhReyvClsOafmxdB%2BaUUoxfamKHkx%2F1WClYEmAVAll%2B8c6%2Bul1pccGznpfVci20WFy%2B2Kz8Z0vQRAn%2BbyDCwFIAXZTKFEw9Exrz8TxKphv8vvvvvCvpvvvvvm2phCv28OvvUnvphvpgvvv96CvpCCvvvm2phCvhhmivpvUvvmvr5JetHoEvpvVmvvC9jaC2QhvCPMMvvm5vpvhvvmv99%3D%3D; _m_h5_tk=ba7b4bb60d1928ecfb712043ff893440_1568608889696; _m_h5_tk_enc=13d361055ac61ac8955b8fd27c2b3ecc; isg=BGxst5y4HUEvUglXpkhyf_yKPUqULRGxMa1hH8atppfy0Q3b7jGYXidp9dlM2Ugn; l=cBIvg05PqiT-VRusBOfZnurza77OUIRb8sPzaNbMiICP9DfWBkNcWZUFnu8XCnGVL65yR3yE78SHBbYSmy4eiR1F1EBn9MpO.',
    ':authority: detail.tmall.com',
    ':method: GET',
    ':path: /item.htm?id=591672684898&skuId=4239716298626',
    ':scheme: https',
    'sec-fetch-mode: navigate',
    'sec-fetch-site: none',
    'sec-fetch-user: ?1',
    'upgrade-insecure-requests: 1',
    ];
//$result =  HttpHelper::get($url,$headers,120);

$file = 'E:/test.html';
$content = file_get_contents($file);
$content = clear($content);
preg_match('#<ulid="J_UlThumb".*?>.*?</ul>#',$content,$match);
//var_dump($content);
//var_dump($match);
$images = [];
if(isset($match[0]) && !empty($match[0])){
    $ee = preg_match_all('#<imgsrc="(.*?)"/>#',$match[0],$match_img);
    if(isset($match_img[1]) && !empty($match_img[1])){
        $images = $match_img[1];
    }
}

foreach($images as &$image){
    $image = 'http:'.$image;
    $point = strripos($image,'_');
    $image = substr($image,0,$point);
    echo '下载：'.$image.PHP_EOL;
    downloadImage($image,'E:\\images\\');
}
echo '主图全部下载完成'.PHP_EOL;

preg_match('#J_TSaleProp.*?>.*?</ul>#',$content,$match_type);
//var_dump($match_type);

$images_type = [];
if(isset($match_type[0]) && !empty($match_type[0])){
    $ee = preg_match_all('#background:url\((.*?)\)#',$match_type[0],$match_img_type);
    //var_dump($match_img_type);die;
    if(isset($match_img_type[1]) && !empty($match_img_type[1])){
        $images_type = $match_img_type[1];
    }
}
//var_dump($images_type);

foreach($images_type as &$image){
    $image = 'http:'.$image;
    $point = strripos($image,'_');
    $image = substr($image,0,$point);
    echo '下载：'.$image.PHP_EOL;
    downloadImage($image,'E:\\images\\');
}
echo '规格图下载完成'.PHP_EOL;