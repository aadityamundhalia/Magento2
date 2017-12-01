<?php
/**
 * Copyright Â© 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Project\OrderReportES\Console\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\{ObjectManager, State};
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\DirectoryList;
use Project\OrderReportES\Helper\Email;
use Project\OrderReportES\Helper\MyHelper;
/**
 * An Abstract class for Indexer related commands.
 */
class OrderReportESCommand extends Command
{
    protected $_productCollectionFactory;
    protected $_date;
    protected $_orderCollectionFactory;
    protected $_helper;
    protected $_myHelper;
    protected $_state;

    public function __construct(
                                State $state,
                                ProductRepositoryInterface $prepo,
                                File $file,
                                DirectoryList $directoryList,
                                \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime,
                                \Magento\Framework\Stdlib\DateTime\DateTime $date,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
                                Email $helper,
                                MyHelper $myHelper
                               )
    {
      $this->_productCollectionFactory = $productCollectionFactory;
      $this->_filesystem = $directoryList;
      $this->_dateTime = $dateTime;
      $this->_file = $file;
      $this->_date = $date;
      $this->_orderCollectionFactory = $orderCollectionFactory;
      $this->_helper = $helper;
      $this->_myHelper = $myHelper;
      $this->_state = $state;
      parent::__construct();
    }

    protected function configure()
    {
        $this->setName('project:OrderReportES')->setDescription('Generate OrderReport - Express Shipping');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      try {
        $this->_state->setAreaCode('frontend');
      } catch (\Magento\Framework\Exception\LocalizedException $e) {
      // Intentionally left empty.
      }
      $time = $this->_date->gmtDate();
      $date = $this->_dateTime->date($time)->format('d-m-Y');
      $csvRows=array();
      $header=['Order number', 'created_at', 'items', 'Bill Name', 'Shipping name', 'Coupon', 'Discount', 'GT (base)', 'GT Purchased', 'Status', 'Shipping Method'];
      $toDate = date('Y-m-d H:i:s', strtotime($time));
      $fromDate = date('Y-m-d H:i:s', strtotime("-70 day", strtotime($time)));
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
        $createdAt = $this->_dateTime->date($createdAt)->format('d-m-Y H:i:s');

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
      }

      if (!empty($csvRows)) {
        $filename = 'OrderReportES-'.$date.'.csv';
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
        echo "\n".$path."\n";

        $storeid = $this->_myHelper->getGeneralConfig('storeId');
        $tomail = $this->_myHelper->getGeneralConfig('tomail');
        $storeid = $this->_myHelper->getGeneralConfig('storeId');
        $tomail = $this->_myHelper->getGeneralConfig('tomail');
        $bcc = $this->_myHelper->getGeneralConfig('bcc');
        $toname = $this->_myHelper->getGeneralConfig('toname');
        $tomail = explode(',', $tomail);
        $bcc = explode(',', $bcc);

        $this->_helper->sendEmail($path, $filename, $storeid, $tomail, $bcc, $toname);
        unlink($path);
      }
    }
}
