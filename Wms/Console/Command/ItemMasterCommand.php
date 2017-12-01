<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Project\Wms\Console\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\{ObjectManager, State};
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\DirectoryList;
/**
 * An Abstract class for Indexer related commands.
 */
class ItemMasterCommand extends Command
{
    protected $_productCollectionFactory;
    protected $_date;

    public function __construct(
                                State $state,
                                ProductRepositoryInterface $prepo,
                                File $file,
                                DirectoryList $directoryList,
                                \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime,
                                \Magento\Framework\Stdlib\DateTime\DateTime $date,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
                               )
    {
      $this->_productCollectionFactory = $productCollectionFactory;
      $this->_filesystem = $directoryList;
      $this->_dateTime = $dateTime;
      $this->_file = $file;
      $this->_date = $date;
      parent::__construct();
    }

    protected function configure()
    {
        $this->setName('project:wms')->setDescription('Generate wms item master');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      echo "Creating WMS item master....\n\n";
      //$time = $this->_dateTime->date()->format('Y-m-d H:i:s');
      $time = $this->_date->gmtDate();
      $toDate = date('Y-m-d H:i:s', strtotime($time));
      $fromDate = date('Y-m-d H:i:s', strtotime("-30 minutes", strtotime($time)));

      $collection = $this->_productCollectionFactory->create()
                                                    //->addAttributeToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate))
                                                    ->addAttributeToSelect('*');
      //$collection->setPageSize(1); // fetching only 3 products

      $result = [];
      foreach ($collection as $product) {
        if($product->getWeight() != null){ $weight =  $product->getWeight(); } else { $weight = ""; }
        if($product->getLenght() != null){ $length =  $product->getLenght(); } else { $length = ""; }
        if($product->getWidth() != null){ $width =  $product->getWidth(); } else { $width = ""; }
        if($product->getHeight() != null){ $height =  $product->getHeight(); } else { $height = ""; }
        if($product->getBubbleWrap() != null){ $wrap =  "Y"; } else { $wrap = "N"; }
        if($product->getSku() != null)
        {
         $result[] = array(
                              'item_number' => $product->getSku(),
                              'barcode' => $product->getBarcode(),
                              'item_desc' => $product->getDescription(),
                              'item_type' => $product->getPrescription(),
                              'item_price' => $product->getPrice(),
                              'stockable' => '"Y"',
                              'unit_weight' => $weight,
                              'unit_lenght' => $length,
                              'unit_width' => $width,
                              'unit_height' => $height,
                              'image_url' => "https://www.pharmacyonline.com.au/media/catalog/product" . $product->getImage(),
                              'wrap' => $wrap
                          );
          echo "Get: " . $product->getName() . "\n";
        }
      }
      if(!empty($result))
      {
          $filename = 'itemMaster.csv';
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
          echo "\n\n---------------------------------------------------------------------------------------\n";
          echo "WMS itemMaster complete. File located at public_html/pub/media/WMS/itemMaster.csv.";
          echo "\n---------------------------------------------------------------------------------------\n\n";
       }
    }
}
