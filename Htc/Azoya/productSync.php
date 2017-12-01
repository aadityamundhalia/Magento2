<?php

require_once 'common.php';

$mode = 'dev';

$provider = $providerCode;

$act = 'product_synchronize';

/**
 * 数据：json格式
 */
$data = $plaintext;

$temp['items'] = json_decode($data,true);
$data = json_encode($temp);
try{
  $Common = new Common($mode, $provider, $act, $data);
  $results = $Common->request();
  $result = json_decode($results);
  if($result){
    if($result->message){
      if($result->message == "successful")
      {
        echo "Azoya Response: success\n";
      }else{
          echo "Azoya Response: ".json_encode($results)."\n";
      }
    }else{
        echo "Azoya Response: ".json_encode($results)."\n";
    }
  }
}
catch (Exception $e) {
  echo 'Caught exception: ',  $e->getMessage(), "\n";
}
//echo $result->message;
