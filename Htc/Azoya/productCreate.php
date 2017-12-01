<?php

/**
 * 接口请求
 */

require_once 'common.php';

$mode = 'dev';

$provider = 'po';

$act = 'product_create';

/**
 * 数据：json格式
 */
$data = '
{
    "sku":"vsdfsef2323",
    "name":"this is simple-product-sku-001",
    "price":2.92,
    "special_price":2.33,
    "currency_code":"AUD",
    "qty":999,
    "is_in_stock":1,
    "status":1,
    "fields":[
        {
            "name":"short_description",
            "value":"this is short description"
        },
        {
            "name":"weight",
            "value":1024
        },
        {
            "name":"country_of_manufacture",
            "value":"KR"
        }
    ]
}
';
/*
$temp['items'][] = json_decode($data,true);
$data = json_encode($temp);

$Common = new Common($mode, $provider, $act, $data);
$results = $Common->request();

echo $results;
*/
