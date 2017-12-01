<?php
require_once 'common.php';

$mode = 'dev';

$provider = 'po';

$act = 'product_create';
$temp['items'] = json_decode($data,true);
$data = json_encode($temp);
$Common = new Common($mode, $provider, $act, $data);
$results = $Common->request();
$result = json_decode($results);
//print_r($result->items);

if($result){
  if($result->message){
    if($result->message == "successful")
    {
      echo "Azoya Response: ".$result->items[0]->message."\n";
      //print_r($result->items);
    }else{
        echo "Azoya Response: ".json_encode($results)."\n";
    }
  }else{
      echo "Azoya Response: ".json_encode($results)."\n";
  }
}
