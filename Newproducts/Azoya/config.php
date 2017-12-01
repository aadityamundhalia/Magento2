<?php

/**
 * api请求配置
 */
use Magento\Framework\App\Bootstrap;
require('/home/pharmacyonline/public_html/app/bootstrap.php');
//require('/var/www/html/aaditya/public_html/app/bootstrap.php');
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$helper = $obj->get('Project\Newproducts\Helper\Data');
$aes_key = $helper->getGeneralConfig('aes_key');
$aes_iv = $helper->getGeneralConfig('aes_iv');
$md5_key = $helper->getGeneralConfig('md5_key');

return [
	'po' => [
		'aes_params' => [
			'aes_key' => $aes_key,
			'aes_iv' => $aes_iv,
			'md5_key' => [
				'dev' => $md5_key,
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
