<?php

/**
 * api请求公共基础类
 */
require_once 'aes.php';

class Common {

	private $mode;			// 请求模式
	private $provider;		// 供应商
	private $act;			// 操作
	private $data;			// 请求数据
	private $config;		// 配置参数
	private $md5KeyPO;
	private $md5KeyCD;
	private $aesKey;
	private $aesIv;

	/**
	 * 初始化
	 */
	public function __construct($mode, $provider, $act, $data) {
		$this->mode = $mode;
		$this->provider = $provider;
		$this->act = $act;
		$this->data = $data;
		$config = include 'config.php';
		$this->config = isset($config[$this->provider]) ? $config[$this->provider] : '';
		$this->md5Key = isset($this->config['aes_params']['md5_key'][$this->mode]) ? $this->config['aes_params']['md5_key'][$this->mode] : '';
		$this->aesKey = isset($this->config['aes_params']['aes_key']) ? $this->config['aes_params']['aes_key'] : '';
		$this->aesIv = isset($this->config['aes_params']['aes_iv']) ? $this->config['aes_params']['aes_iv'] : '';
	}

	/**
	 * 请求接口
	 */
	public function request() {
		$url = isset($this->config['api_list'][$this->act][$this->mode]) ? $this->config['api_list'][$this->act][$this->mode] : '';
		$ip = isset($this->config['request_ip']) ? $this->config['request_ip'] : '127.0.0.1';
		$provider = $this->provider;
		$content = $this->encrypt($this->data);
		$sign = md5($content . $this->md5Key);
		$params = "request_ip=$ip&provider_code=$provider&sign=$sign&content=$content";
		$results = $this->httpPost($url, $params);
		return $results;
	}

	/**
	 * 加密请求数据
	 */
	public function encrypt($data) {
		if (isset($this->config['mcrypt']) && $this->config['mcrypt']) {
			$encrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->aesKey, $data, MCRYPT_MODE_CBC, $this->aesIv);
		} else {
			$step1 = Crypt_AES::strToHex($this->aesKey);
			$step2 = Crypt_AES::hex2bin($step1);
			$step3  = Crypt_AES::strToHex($this->aesIv);
			$aes = new Crypt_AES($step2, $step3, array('PKCS7'=>true, 'mode'=>'cbc'));
			$encrypt = $aes->encrypt($data);
		}
		$content = urlencode(base64_encode($encrypt));
		return $content;
	}

	/**
	 * post请求
	 */
	function httpPost($url, $params){
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    if(stripos($url, "https://") !== false) {
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    }
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	    $results = curl_exec($ch);
	    return $results;
	}


}
