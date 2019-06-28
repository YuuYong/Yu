<?php
namespace sdk\libs;
/**
 * curl
 * @package common
 */

/**
 * 用curl拿http请求类
 * @author chenwei
 * @package common_lib
 */
class HttpHelper {
	/**
	 * [__construct description]
	 */
	private function __construct() {
		return;
	}


	public static function post($url, $postdata, $header=array(), $timeout=5, $cert = array()) {
        $enable_proxy = false;
	    if($enable_proxy){
	        $is_proxy = 0;
	    } else {
	        $is_proxy = 1;
	    }

		if (!function_exists('curl_init')) {
			throw new \Exception('server not install curl');
		}
		$header[] = 'Expect:';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postdata );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
        
        $url_array = parse_url ( $url );
        if ($url_array ['scheme'] == 'https') {
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 ); // 对认证证书来源的检查
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 2 ); // 从证书中检查SSL加密算法是否存在
			if(isset($cert['cert'])){
                curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
                curl_setopt($ch, CURLOPT_SSLCERT, $cert['cert']);
            }
            if(isset($cert['key'])){
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
		if(count($arrtemp) <2 ){
			curl_close($ch);
		    return '';
		}


		if(1  || empty($is_proxy )){
		    list($header, $data) = explode("\r\n\r\n", $data);
		} else {
		    if($url_array['scheme']=='https'){
		        $header = $arrtemp[1];
		        $data = $arrtemp[2];
		    } else {
		        list($header, $data) = explode("\r\n\r\n", $data);
		    }
		}
		
		
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code == 301 || $http_code == 302) { 
            $url = self::getLocationUrl($header, $url_array, $requrl);
            
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_HEADER, false);
		    
		    $data = curl_exec($ch);
		}

		if($http_code>=400){
		    $log_path = SDK_PATH . "/../log/httplog/" . PJNAME . '/';
		    $logger = new \Katzgrau\KLogger\Logger($log_path, \Psr\Log\LogLevel::DEBUG);
		    $logger->critical("{$requrl},".json_encode($postdata).",http_code:{$http_code}");
		}

		if ($data == false) {
			$m_error = curl_error($ch);
			curl_close($ch);
			$log_path = SDK_PATH . "/../log/httperrorpostlog/" . PJNAME . '/';
			$logger = new \Katzgrau\KLogger\Logger($log_path, \Psr\Log\LogLevel::DEBUG);
			$logger->critical("url:{$requrl},data:".json_encode($postdata)."\nerror_msg:{$m_error}\n");
		}
		@curl_close($ch);
		return $data;
	}
        
        


	/**
	 * 发送get请求
	 * @param string $req
	 * @param array $header
	 * @param int $timeout
	 * @throws Exception
	 */
	public static function get($req, $header=array(), $timeout=5) {
	    $proxy_config = \sdk\config\Config::httpProxy();
	    if(empty($proxy_config["enable"])){
	        $is_proxy = 0;
	    } else {
	        $is_proxy = 1;
	    }
	    
	    
		$url = self::makeUri($req);
		if (!function_exists('curl_init')) {
			throw new \Exception('server not install curl');
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		
        $url_array = parse_url ( $url );
        if ($url_array ['scheme'] == 'https') {
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 ); // 对认证证书来源的检查
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 2 ); // 从证书中检查SSL加密算法是否存在
        }
        
        //根据host判断是否代理
        if(!empty($proxy_config["no_proxy_hosts"]) && in_array($url_array["host"], $proxy_config["no_proxy_hosts"])){
            $is_proxy = 0;
        }
        
        
        if($is_proxy == 1){
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); //代理服务器地址	
            curl_setopt($ch, CURLOPT_PROXY, $proxy_config["ip"]); //代理服务器地址
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_config["port"]); //代理服务器端口
        }
                
                
		if (!empty($header)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		$data = curl_exec($ch);
		if ($data == false) {
			$m_error = curl_error($ch);
			$log_path = SDK_PATH . "/../log/httperrorblog/" . PJNAME . '/';
			$logger = new \Katzgrau\KLogger\Logger($log_path, \Psr\Log\LogLevel::DEBUG);
			$logger->critical("{$req},error_msg:{$m_error}\n");
		}
		$arrtemp = explode("\r\n\r\n", $data);
		if(count($arrtemp) <2 ){
			curl_close($ch);
		    return '';
		}
		
		
		if(1  || empty($is_proxy )){
		    list($header, $data) = explode("\r\n\r\n", $data);
		} else {
		    if($url_array['scheme']=='https'){
		        $header = $arrtemp[1];
		        $data = $arrtemp[2];
		    } else {
		        list($header, $data) = explode("\r\n\r\n", $data);
		    }
		}
		
		
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


		
		if ($http_code == 301 || $http_code == 302) {
		    $url = self::getLocationUrl($header, $url_array, $req);
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_HEADER, false);
		    $data = curl_exec($ch);
		}

		if($http_code>=400){
		    $log_path = SDK_PATH . "/../log/httplog/" . PJNAME . '/';
		    $logger = new \Katzgrau\KLogger\Logger($log_path, \Psr\Log\LogLevel::DEBUG);
		    $logger->critical("{$req},".",http_code:{$http_code}");
		}

		if ($data == false) {
			$m_error = curl_error($ch);
			curl_close($ch);
			$log_path = SDK_PATH . "/../log/httperrorlog/" . PJNAME . '/';
			$logger = new \Katzgrau\KLogger\Logger($log_path, \Psr\Log\LogLevel::DEBUG);
			$logger->critical("{$req},error_msg:{$m_error}\n");
		}
		@curl_close($ch);
		return $data;
	}

}
?>