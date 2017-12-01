<?php

/**
 * 接口请求
 */

include 'common.php';

$mode = 'dev';

$provider = 'po';

$act = 'product_synchronize';

/**
 * 数据：json格式
 */
$data = $plaintext;

$temp['items'][] = json_decode($data,true);
$data = json_encode($temp);

$Common = new Common($mode, $provider, $act, $data);
$results = $Common->request();

echo "Azoya Response: ".$results."\n";
