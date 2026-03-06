<?php
namespace MageArray\CheckDelivery\Model;

use Magento\Framework\HTTP\Client\Curl;

class OpenSearchLookup{

protected $curl;

public function __construct(Curl $curl){
$this->curl=$curl;
}

public function checkPincode($pincode){

$url="http://localhost:9200/pincode_index/_search?q=pincode:".$pincode;

$this->curl->get($url);
$response=$this->curl->getBody();

$data=json_decode($response,true);

if(isset($data['hits']['hits'][0])){
return $data['hits']['hits'][0]['_source'];
}

return false;

}
}
