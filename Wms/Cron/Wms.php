<?php
namespace Project\Wms\Cron;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\DirectoryList;

class Wms {
   protected $_logger;
   protected $_productCollectionFactory;
   protected $_dateTime;
   protected $_filesystem;
   protected $_file;
   protected $_date;

   public function __construct(
       \Psr\Log\LoggerInterface $logger,
       \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
       \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime,
       \Magento\Framework\Stdlib\DateTime\DateTime $date,
       File $file,
       DirectoryList $directoryList
   )
    {
     $this->_logger = $logger;
     $this->_productCollectionFactory = $productCollectionFactory;
     $this->_dateTime = $dateTime;
     $this->_filesystem = $directoryList;
     $this->_file = $file;
     $this->_date = $date;
    }

   public function execute()
   {
       $time = $this->_date->gmtDate();
       $toDate = date('Y-m-d H:i:s', strtotime($time));
       $fromDate = date('Y-m-d H:i:s', strtotime("-30 minutes", strtotime($time)));
       $collection = $this->_productCollectionFactory->create()
                                                     ->addAttributeToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate))
                                                     ->addAttributeToSelect('*');
       //$collection->setPageSize(1); // fetching only 3 products

       //$result = [];
       $result = array();
       foreach ($collection as $product) {
          if($product->getWeight() != null){ $weight =  $product->getWeight(); } else { $weight = ""; }
          if($product->getLenght() != null){ $length =  $product->getLenght(); } else { $length = ""; }
          if($product->getWidth() != null){ $width =  $product->getWidth(); } else { $width = ""; }
          if($product->getHeight() != null){ $height =  $product->getHeight(); } else { $height = ""; }
          if($product->getBubbleWrap() != null){ $wrap =  "Y"; } else { $wrap = "N"; }
          if($product->getPrescription() == 0){ $item_type =  "OTC"; } else { $item_type = "PRE"; }
          if($product->getSku() != null)
          {
            $result[] = array(
                                'item_number' => $product->getSku(),
                                'barcode' => $product->getBarcode(),
                                'item_desc' => $product->getName(),
                                'item_type' => $item_type,
                                'item_price' => $product->getPrice(),
                                'stockable' => '"Y"',
                                'unit_weight' => $weight,
                                'unit_lenght' => $length,
                                'unit_width' => $width,
                                'unit_height' => $height,
                                'image_url' => "https://www.pharmacyonline.com.au/media/catalog/product" . $product->getImage(),
                                'wrap' => $wrap
                             );
          }
       }
       if(!empty($result))
       {
           $filename = 'ITM'.date("Ymdhisa") . '.txt';
           $filePath = $this->_filesystem->getPath('media') . '/WMS/';
           if (!is_dir($filePath)) {
               $ioAdapter = $this->_file;
               $ioAdapter->mkdir($filePath, 0775);
           }
           $fp = fopen($filePath . $filename, 'w');
           foreach ($result as $file) {
               $result = [];
               array_walk_recursive($file, function($item) use (&$result) {
                   $result[] = $item;
               });
               fputcsv($fp, $result);
           }
           fclose($fp);
           	$this->_logger->debug('WMS cron ran successfully');
       }
   }
}
