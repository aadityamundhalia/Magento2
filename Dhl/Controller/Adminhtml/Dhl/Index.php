<?php
namespace Project\Dhl\Controller\Adminhtml\Dhl;

use Magento\Backend\App\Action\Context;
use Project\Dhl\Model\Zdhl;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const CUSTOMER_WEBSITE_ID = 1;
    protected $_zdhlFactory;
    protected $_storeManager;
    protected $_order;
    protected $_countryFactory;
    protected $_coreRegistry;
    protected $_resultPageFactory;

    public function __construct(Context $context,
                                Zdhl $zdhlFactory,
                                StoreManagerInterface $storeManager,
                                OrderInterface $order,
                                CountryFactory $countryFactory,
                                Registry $coreRegistry,
                                PageFactory $pageFactory
                               )
    {
        $this->_zdhlFactory = $zdhlFactory;
        $this->_storeManager = $storeManager;
        $this->_order = $order;
        $this->_countryFactory = $countryFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
      $boxesWG = array();
      $milkP = array();
      $post = $this->getRequest()->getPostValue();
      //echo json_encode($post);
      //echo "<br>";
      $orderNo=$post["orderNo"];
      if(isset($post["rSending"])){
        $rSending=$post["rSending"];
      }else {
        $rSending = '';
      }

      $boxesWG[]=$post["box1"];
      $boxesWG[]=$post["box2"];
      $boxesWG[]=$post["box3"];
      $boxesWG[]=$post["box4"];

      if(isset($post["milkP1"])){
        $milkP[]=$post["milkP1"];
      }else {
        $milkP[]="";
      }
      if(isset($post["milkP2"])){
        $milkP[]=$post["milkP2"];
      }else {
        $milkP[]="";
      }
      if(isset($post["milkP3"])){
        $milkP[]=$post["milkP3"];
      }else {
        $milkP[]="";
      }
      if(isset($post["milkP4"])){
        $milkP[]=$post["milkP4"];
      }else {
        $milkP[]="";
      }

      $this->_storeManager->setCurrentStore(0);
      $order = $this->_order->loadByIncrementId($orderNo);
      $address = $order->getShippingAddress();
      $email = $order->getCustomerEmail();
      $consigneeName = $address->getFirstname().' '.$address->getLastname();
      $addressComponents = $address->getStreet();
      $address1 = $addressComponents[0];
      $address2 = '';
      if(count($addressComponents) > 1){ $address2 = $addressComponents[1]; }
      $city = $address->getCity();
      if(is_null($city))$city='';
      $state = $address->getRegion();
      if(is_null($state))$state='';
      $postcode = $address->getPostcode();
      $phone = $address->getTelephone();
      $countryCode = $address->getCountryId();
      $totalValue = $order->getSubtotal();
      $country = $this->_countryFactory->create()->loadByCode($countryCode);
      $countryName = $country->getName();

      $barcodeArray = array();

      for($i = 0; $i <= 3; $i++) {
        $productCode = '';
        $weight = $boxesWG[$i];
        $packageNo=$orderNo;

        if($rSending=='y'){
          $packageNo=$packageNo . 'R';
        }

        $packageNo=$packageNo . $i;

        if($weight==''){
          continue;
        }

        if($milkP[$i]=='y' and $countryCode == 'CN'){
    			$productCode = '999';
    		}

        $data = array( 'totalValue' => $totalValue,
    				           'countryCode' => $countryCode,
    				           'email' => $email,
    				           'phone' => $phone,
    				           'postcode' => $postcode,
    				           'state' => $state,
    				           'city' => $city,
    				           'address2' => $address2,
    				           'address1' => $address1,
    				           'name' => $consigneeName,
    				           'orderNo' => $orderNo,
    				           'weight' => $weight,
    				           'productCode' => $productCode,
    				           'createdAt' => date("Y-m-d H:i:s"),
    					         'packageNo' => $packageNo
    		              );

    		if ($weight<=2000){
    			$shippingCode = 'PPS';
    			$topLeft = '<div>PACKET PLUS</div><div>STANDARD</div>';
    		}else {
          $shippingCode = 'PLD';
    		  $topLeft = '<div>PARCEL</div><div>STANDARD</div>';
    		}
        try {
                  $add = $this->_zdhlFactory;
                  $add->setData($data)->save();
            } catch (Exception $e) {
                  echo $e->getMessage();
            }
        $barcode = "AUMDL$packageNo";
        $barcodeArray[] = array(
                                'topLeft' => $topLeft,
                                'barcode' => $barcode,
                                'consigneeName' => $consigneeName,
                                'address1' => $address1,
                                'address2' => $address2,
                                'city' => $city,
                                'state' => $state,
                                'postcode' => $postcode,
                                'countryName' => $countryName
                               );
      }
      $resultPage = $this->_resultPageFactory->create();
      $this->_coreRegistry->register('barcode', $barcodeArray);
      return $resultPage;
    }
    protected function _isAllowed()
    {
      return $this->_authorization->isAllowed('Project_Dhl::barcode');
    }
}
