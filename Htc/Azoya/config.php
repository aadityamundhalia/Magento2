<?php

/**
 * api请求配置
 */
use Magento\Framework\App\Bootstrap;
require('/home/pharmacyonline/public_html/app/bootstrap.php');
//require('/var/www/html/aaditya/public_html/app/bootstrap.php');
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$helper = $obj->get('Project\Htc\Helper\Data');
$aes_key = $helper->getGeneralConfig('aes_key');
$aes_iv = $helper->getGeneralConfig('aes_iv');
$md5_key_po = $helper->getGeneralConfig('md5_key_po');
$md5_key_cd = $helper->getGeneralConfig('md5_key_cd');


return [
	'po' => [
		'aes_params' => [
			'aes_key' => $aes_key,
			'aes_iv' => $aes_iv,
			'md5_key' => [
				'dev' => $md5_key_po,
			]
		],
		'api_list' => [
						'product_create' => [
								'dev' => 'http://apitest.azoyagroup.com/v1/catalog/product/create',
						],
            'product_synchronize' => [
                'dev' => 'http://apitest.azoyagroup.com/v1/catalog/product/synchronize',
            ],
		],
		'mcrypt' => false
	],
	'cd' => [
		'aes_params' => [
			'aes_key' => $aes_key,
			'aes_iv' => $aes_iv,
			'md5_key' => [
				'dev' => $md5_key_cd,
			]
		],
		'api_list' => [
						'product_create' => [
								'dev' => 'http://apitest.azoyagroup.com/v1/catalog/product/create',
						],
            'product_synchronize' => [
                'dev' => 'http://apitest.azoyagroup.com/v1/catalog/product/synchronize',
            ],
		],
		'mcrypt' => false
	],
];
