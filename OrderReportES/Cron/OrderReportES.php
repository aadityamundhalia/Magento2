<?php
namespace Project\OrderReportES\Cron;

class OrderReportES {
   protected $_logger;
   protected $_productCollectionFactory;
   protected $_dateTime;
   protected $_filesystem;
   protected $_file;
   protected $_date;
   protected $_orderCollectionFactory;
   protected $_helper;
   protected $_myHelper;

   public function __construct(
       \Psr\Log\LoggerInterface $logger,
       \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
       \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime,
       \Magento\Framework\Stdlib\DateTime\DateTime $date,
       \Magento\Framework\Filesystem\Io\File $file,
       \Magento\Framework\Filesystem\DirectoryList $directoryList,
       \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
       \Project\OrderReportES\Helper\Email $helper,
       \Project\OrderReportES\Helper\MyHelper $myHelper
   )
    {
     $this->_logger = $logger;
     $this->_productCollectionFactory = $productCollectionFactory;
     $this->_dateTime = $dateTime;
     $this->_filesystem = $directoryList;
     $this->_file = $file;
     $this->_date = $date;
     $this->_orderCollectionFactory = $orderCollectionFactory;
     $this->_helper = $helper;
     $this->_myHelper = $myHelper;
    }

   public function execute()
   {
      $csvRows=array();
      $header=['Order number', 'created_at', 'items', 'Bill Name', 'Shipping name', 'Coupon', 'Discount', 'GT (base)', 'GT Purchased', 'Status', 'Shipping Method'];
      //$csvRows[]=$header;
      $time = $this->_date->gmtDate();
      $toDate = date('Y-m-d H:i:s', strtotime($time));
      $fromDate = date('Y-m-d H:i:s', strtotime("-10 day", strtotime($time)));
      $collection = $this->_orderCollectionFactory->create()
                         ->addAttributeToSelect('*')
                         ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
                         ->addAttributeToFilter('status', array('neq' => 'closed'))
                         ->addAttributeToFilter('status', array('neq' => 'canceled'))
                         ->addAttributeToFilter('status', array('neq' => 'paypal_canceled_reversal'))
                         ->addAttributeToFilter('status', array('neq' => 'paypal_reversed'))
                         ->addAttributeToFilter('status', array('neq' => 'complete'))
                         ->addAttributeToFilter('status', array('neq' => 'fraud'))
                         ->setOrder('created_at', 'ASC');
      foreach ($collection as $order) {
        $orderItems = $order->getItemsCollection()
                            ->addAttributeToSelect('*')
                            ->load();

        $orderId= $order->getIncrementId();
        $createdAt= $order->getCreatedAt();
        if($order->getBillingAddress() !== null) {
          $billName=$order->getBillingAddress()->getName();
        } else {
          $billName = "";
        }
        if($order->getShippingAddress() !== null) {
          $shipName=$order->getShippingAddress()->getName();
        } else {
          $shipName = "";
        }
        $coupon= $order->getCouponCode();
        $discount=$order->getDiscountAmount();
        $baseGrandTotal= $order->getBaseGrandTotal();
        $grandTotal= $order->getGrandTotal();
        $status= $order->getStatus();
        $shippingDesc= $order->getShippingDescription();
        $orderItemsStr=null;

        foreach($orderItems as $sItem) {
          $orderItemsStr .= $sItem->getName().' (QTY: '.round($sItem->getQtyInvoiced()).'); ';
        }
        $orderItemsStr=$orderItemsStr;

        if($shippingDesc == "AU Post - Australia Post Express") {
        $csvRows[]=array($orderId, $createdAt, $orderItemsStr, $billName, $shipName, $coupon,
                         $discount, $baseGrandTotal, $grandTotal, $status,
                         $shippingDesc
                       );
        }
        //break;
      }

      if (!empty($csvRows)) {
        $filename = 'OrderReportES-'.$time.'.csv';
        $filePath = $this->_filesystem->getPath('media') . '/OrderReportES/';
        $path = $filePath . $filename;
        if (!is_dir($filePath)) {
            $ioAdapter = $this->_file;
            $ioAdapter->mkdir($filePath, 0775);
        }
        $fp = fopen($filePath . $filename, 'w');
        fputcsv($fp, $header);
        foreach($csvRows as $csvRow){
            fputcsv($fp, $csvRow);
        }
        fclose($fp);
        $storeid = $this->_myHelper->getGeneralConfig('storeId');
        $tomail = $this->_myHelper->getGeneralConfig('tomail');
        $storeid = $this->_myHelper->getGeneralConfig('storeId');
        $tomail = $this->_myHelper->getGeneralConfig('tomail');
        $bcc = $this->_myHelper->getGeneralConfig('bcc');
        $toname = $this->_myHelper->getGeneralConfig('toname');
        $tomail = explode(',', $tomail);
        $bcc = explode(',', $bcc);

        $this->_helper->sendEmail($path, $filename, $storeid, $tomail, $bcc, $toname);
        //unlink($path);
      }
   }
}
