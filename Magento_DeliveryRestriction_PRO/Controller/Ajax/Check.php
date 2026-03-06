<?php
namespace MageArray\CheckDelivery\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use MageArray\CheckDelivery\Model\OpenSearchLookup;

class Check extends Action{

protected $jsonFactory;
protected $lookup;

public function __construct(
Context $context,
JsonFactory $jsonFactory,
OpenSearchLookup $lookup
){
parent::__construct($context);
$this->jsonFactory=$jsonFactory;
$this->lookup=$lookup;
}

public function execute(){

$result=$this->jsonFactory->create();
$pincode=$this->getRequest()->getParam('pincode');

if(!preg_match('/^[0-9]{6}$/',$pincode)){
return $result->setData(['success'=>false,'message'=>'Invalid pincode']);
}

$data=$this->lookup->checkPincode($pincode);

if(!$data){
return $result->setData(['success'=>false,'message'=>'Delivery not available']);
}

$msg="Delivery available. Estimated ".$data['delivery_days']." days.";

return $result->setData(['success'=>true,'message'=>$msg]);

}
}
