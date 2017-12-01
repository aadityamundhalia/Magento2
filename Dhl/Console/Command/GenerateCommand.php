<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Project\Dhl\Console\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\{ObjectManager, State};
use Project\Dhl\Model\Zdhl;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Locale\Resolver;
use Project\Dhl\Helper\Data;
/**
 * An Abstract class for Indexer related commands.
 */
class GenerateCommand extends Command
{
    protected $_zdhlFactory;
    protected $_storeManager;
    protected $_order;
    protected $_countryFactory;
    protected $_coreRegistry;
    protected $_resultPageFactory;
    protected $_filesystem;
    protected $_file;
    protected $_resolver;
    protected $_convertor;
    protected $_transaction;
    protected $_email;
    protected $_invoiceService;
    protected $_orderRepository;
    protected $_productCollectionFactory;
    protected $_shipmentNotifier;
    protected $_helper;

    public function __construct(
                                Zdhl $zdhlFactory,
                                StoreManagerInterface $storeManager,
                                OrderInterface $order,
                                CountryFactory $countryFactory,
                                Registry $coreRegistry,
                                PageFactory $pageFactory,
                                DirectoryList $directoryList,
                                File $file,
                                Resolver $resolver,
                                \Magento\Sales\Model\Convert\Order $convertor,
                                \Magento\Framework\DB\Transaction $transaction,
                                \Magento\Sales\Model\Order\Shipment\Sender\EmailSender $email,
                                \Magento\Sales\Model\Service\InvoiceService $invoiceService,
                                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                State $state,
                                ProductRepositoryInterface $prepo,
                                \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                Data $helper
                               )
    {
      $this->_zdhlFactory = $zdhlFactory;
      $this->_storeManager = $storeManager;
      $this->_order = $order;
      $this->_countryFactory = $countryFactory;
      $this->_coreRegistry = $coreRegistry;
      $this->_resultPageFactory = $pageFactory;
      $this->_filesystem = $directoryList;
      $this->_file = $file;
      $this->_resolver = $resolver;
      $this->_convertor = $convertor;
      $this->_transaction = $transaction;
      $this->_email = $email;
      $this->_invoiceService = $invoiceService;
      $this->_productCollectionFactory = $productCollectionFactory;
      $this->_shipmentNotifier = $shipmentNotifier;
      $this->_helper = $helper;
      try {
        //$state->setAreaCode('frontend');
      } catch (\Magento\Framework\Exception\LocalizedException $e) {
      // Intentionally left empty.
      }
    parent::__construct();
    }

    protected function configure()
    {
        $this->setName('project:generate')->setDescription('Generate Dhl orders from table zdhl_orders');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "\n\nInitiating...\n";
        $this->_storeManager->setCurrentStore(0);
        $filename = date("Ymdhisa") . '.txt';

        echo "Setting up file path\n";

        $filePath = $this->_filesystem->getPath('media') . '/DHL/';
        if (!is_dir($filePath)) {
            $ioAdapter = $this->_file;
            $ioAdapter->mkdir($filePath, 0775);
        }
        $fp = fopen($filePath . $filename,'w');
        $accountNumber = $this->_helper->getGeneralConfig('accountNumber');

        $csvHeader = array(
            "Pick-up Account Number",
            "Sales Channel",
            "Shipment Order ID",
            "Tracking Number",
            "Shipping Service Code",
            "Company",
            "Consignee Name",
            "Address Line 1",
            "Address Line 2",
            "Address Line 3",
            "City",
            "State",
            "Postal Code",
            "Destination Country Code",
            "Phone Number",
            "Email Address",
            "Shipment Weight (g)",
            "Length (cm)",
            "Width (cm)",
            "Height (cm)",
            "Currency Code",
            "Total Declared Value",
            "Incoterm",
            "Freight",
            "Is Insured",
            "Insurance",
            "Is COD",
            "Cash on Delivery Value",
            "Recipient ID",
            "Recipient ID Type",
            "Duties",
            "Taxes",
            "Workshare Indicator",
            "Shipment Description",
            "Shipment Import Description",
            "Shipment Export Description",
            "Shipment Content Indicator",
            "Content Description",
            "Content Import Description",
            "Content Export Description",
            "Content Unit Price",
            "Content Origin Country",
            "Content Quantity",
            "Content Weight (g)",
            "Content Code",
            "HS Code",
            "Content Indicator",
            "Remarks",
            "Shipper Company",
            "Shipper Name",
            "Shipper Address1",
            "Shipper Address2",
            "Shipper Address3",
            "Shipper City",
            "Shipper State",
            "Shipper Postal Code",
            "Shipper CountryCode",
            "Shipper Phone Number",
            "Shipper Email address",
            "Service1",
            "Service2",
            "Service3",
            "Service4",
            "Service5",
            "Grouping Reference1",
            "Grouping Reference2",
            "Customer Reference 1",
            "Customer Reference 2"
        );
        fputs($fp,"\xEF\xBB\xBF" . implode($csvHeader,"\t") . "\r\n");
        //->getData()
        $records = $this->_zdhlFactory->getCollection();
        foreach ($records as $row) {
          echo ".";
          $id = $row->getZdhlId();
          $totalValue = $row->getData('totalValue');
          $countryCode = $row->getData('countryCode');
          $email = $row->getEmail();
          $phone = $row->getPhone();
          $postcode = $row->getPostcode();
          $state = $row->getState();
          $city = $row->getCity();
          $address2 = $row->getData('address2');
          $address1 = $row->getData('address1');
          $name = $row->getName();
          $orderNo = $row->getData('orderNo');
          $packageNo = $row->getData('packageNo');
          $weight = $row->getWeight();
          if ($weight < 2000){
              $postSCode = 'PPS';
          }else{
              $postSCode = 'PLD';
          }
          $productCode = $row->getData('productCode');
          if ($productCode == '999' and $countryCode == 'CN'){
              $genericGoodsD = 'milk powder';
          }else{
              $genericGoodsD = 'health goods';
              $productCode = '.';
          }
          $fileCreated = $row->getFileCreated();
          $customerRef = $packageNo;
          $product_row = array(
              $accountNumber,
              "",
              $customerRef,
              "",
              $postSCode,
              "",
              $name,
              $address1,
              $address2,
              "",
              $city,
              $state,
              $postcode,
              $countryCode,
              $phone,
              "",
              $weight,
              "",
              "",
              "",
              "AUD",
              $totalValue,
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              $genericGoodsD,
              "",
              "",
              "",
              $genericGoodsD,
              "",
              "",
              $totalValue,
              "",
              "1",
              "",
              $productCode,
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "",
              "");
          fputs($fp,implode($product_row,"\t") . "\r\n");
          $fileCreatedAt = date("Y-m-d H:i:s");
          $row->setData('fileCreatedAt', $fileCreatedAt);
          $row->save();

          $order = $this->_order->loadByIncrementId($orderNo);
          if ($order->getId()){
            $this->_storeManager->setCurrentStore($order->getStoreId());
            $this->_resolver->getLocale();
            $this->_resolver->emulate($order->getStoreId());

            if ($order->canShip()){
              $this->ship($order);
            }
            $this->_resolver->getLocale();
            $this->_resolver->revert();
            sleep(2);
            $list = array();

            foreach (glob($this->_filesystem->getPath('media') . '/DHL/*.txt') as $f) {
              $list[filectime($f)] = $f;
            }
          }
        }
        echo "\n---------------------------------------\n";
        echo "File generation completed.";
        echo "\n---------------------------------------\n";
    }
    private function ship($order)
    {
        $comment = "Email";
        $includeComment = false;
        $shipment = $this->_convertor->toShipment($order);
        foreach ($order->getAllItems() as $orderItem)
        {
            if (!$orderItem->getQtyToShip())
            {
                continue;
            }
            if ($orderItem->getIsVirtual())
            {
                continue;
            }

            $item = $this->_convertor->itemToShipmentItem($orderItem);
            $qty = $orderItem->getQtyToShip();
            $item->setQty($qty);
            $shipment->addItem($item);
        }
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        try {
                // Save created shipment and order
                $shipment->save();
                $shipment->getOrder()->save();

                // Send email
                //echo "\n Sending email to customer... \n";
                try {
                    $this->_shipmentNotifier->notify($shipment);
                } catch (Exception $e) {
                    echo "\n Failed to send email to customer... \n";
                }

                $shipment->save();
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                                __($e->getMessage())
                            );
            }

         $SaveTrans = $this->_transaction->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
         //$this->_email->send($order,$shipment,null,false);
    }
}
