<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Project\Newproducts\Console\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\{ObjectManager, State};
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Store\Model\StoreRepository;
use Magento\Catalog\Model\ProductFactory;

class ProductCreateCommand extends Command
{
    protected $_productCollectionFactory;
    protected $_dateTime;
    protected $_filesystem;
    protected $_file;
    protected $_category;
    protected $_stock;
    protected $_store;
    protected $_stockState;
    protected $_productFactory;
    protected $_storeManager;
    protected $_urlInterface;
    protected $_resourceCon;
    protected $_state;

    public function __construct(
                                State $state,
                                ProductRepositoryInterface $prepo,
                                File $file,
                                DirectoryList $directoryList,
                                \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                StoreRepository $store,
                                \Magento\Catalog\Model\CategoryFactory $category,
                                \Magento\CatalogInventory\Api\StockRegistryInterface $stock,
                                \Magento\CatalogInventory\Api\StockStateInterface $stockState,
                                ProductFactory $productFactory,
                                \Magento\Framework\UrlInterface $urlInterface,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Framework\App\ResourceConnection $resourceCon
                               )
    {
      $this->_productCollectionFactory = $productCollectionFactory;
      $this->_filesystem = $directoryList;
      $this->_dateTime = $dateTime;
      $this->_file = $file;
      $this->_category = $category;
      $this->_stock = $stock;
      $this->_store = $store;
      $this->_stockState = $stockState;
      $this->_productFactory = $productFactory;
      $this->_storeManager = $storeManager;
      $this->_urlInterface = $urlInterface;
      $this->_resourceCon = $resourceCon;
      $this->_state = $state;
      parent::__construct();
    }

    protected function configure()
    {
        $this->setName('project:Newproducts')->setDescription('Get New products and upload to Azoya');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      try {
      $this->_state->setAreaCode('frontend');
      } catch (\Magento\Framework\Exception\LocalizedException $e) {
      // Intentionally left empty.
      }
      $connection = $this->_resourceCon->getConnection();
      $tableName = $this->_resourceCon->getTableName('ewave_aa_options'); //gives table name with prefix

      echo "Initiating process...\n";
      $filename = date("Ymdhisa");
      $filePath = $this->_filesystem->getPath('media') . '/NewProducts/';
      if (!is_dir($filePath)) {
          $ioAdapter = $this->_file;
          $ioAdapter->mkdir($filePath, 0775);
      }
      $fpPO = fopen($filePath . $filename, 'w');
      $first =  date( "Y-m-d h:m:s", strtotime( "now -2 week" ) );
//echo date( "Y-m-d H:m:s", strtotime( "now -2 week" ) );
      $roundlimit = 500;
      $max = $this->_productCollectionFactory->create()
                   ->addAttributeToSelect('*')
                   ->addAttributeToFilter('prescription', 0)  //To Be uncommented
                   ->addAttributeToFilter('chemist', 0) //To Be uncommented
                   ->addAttributeToFilter('created_at', ['gteq' =>$first])
                   ->addAttributeToFilter('flammable', 0)
                   ->addAttributeToFilter('refrigerated', 0)
                   ->load();

      $max = $max->getSize();
      $maxRound = round($max/$roundlimit);
      if($maxRound < $max){
        $maxRound = $maxRound+1;
      }

      echo "\nTotal: ".$max."\n";

      for ($i=1; $i <= $maxRound; $i++) {
        echo "\n\nRunning batch: ".$i."/".$maxRound."\n";
        $product_row = array();
        $products = $this->_productCollectionFactory->create()
                   ->addAttributeToSelect('*')
                   ->addAttributeToFilter('prescription', 0)  //To Be uncommented
                   ->addAttributeToFilter('chemist', 0) //To Be uncommented
                   ->addAttributeToFilter('created_at', ['gteq' =>$first])
                   ->addAttributeToFilter('flammable', 0)
                   ->addAttributeToFilter('refrigerated', 0)
                   ->setPage($i, $roundlimit)
                   ->load();

         foreach ( $products as $_product ){
            echo ".";
            $sku = $_product->getSku();
            $entityId = $_product->getEntityId();
            $name = $this->addThis($_product->getName());
            $weight = $_product->getWeight();
            if($weight == null)
            {
                $weight = "";
            }
            $description = $this->addThis($_product->getDescription());
            $brand = $_product->getBrand();
            //$brand = null;
            if(!is_null($brand))
            {
              $sql = "Select option_id, meta_title FROM ".$tableName." where option_id = ".$brand." limit 1";
              $result = $connection->fetchAll($sql);
              $brand = $result[0]['meta_title'];
            }
            $rrp= $_product->getMsrp();
            $barcode = $_product->getBarcode();
            $cat = $_product->getCategoryIds();
            $catCount = count($cat);
            $arrayLength=0;
            //$status = $_product->getQuantityAndStockStatus();
            $status = $_product->getStatus();

            $image = $_product->getImage();
            if(substr($image,-4,1)=='.') {
               $image_link =  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'.$image;
            }
            else {
               continue;
            }

            $price = $_product->getChinaPrice();
            if(is_null($price) or $price=='')
            {
               $price = $_product->getFinalPrice();
            }
            $price_num = $price + 0;
            $stock = $this->_stock->getStockItem($_product->getId());
            $qty = $stock->getQty();

            if ($stock->getIsInStock()) {
               $stockStatus = 1;
            }
            else {
              $stockStatus = 0;
              $qty = 0;
            }

            if(is_null($brand)){
              $brand = "";
            }
            if(is_null($barcode)){
              $barcode = "";
            }
            $taxRate = $_product->getTaxClassId();
            if($status == 1){
              $product_row[] = array(
                                     "sku" => $sku,
                                     "name" => $name,
                                     "price" => $price,
                                     "special_price" => $price_num,
                                     "currency_code" => 'AUD',
                                     "qty" => $qty,
                                     "is_in_stock" => $stockStatus,
                                     "status" => 1,
                                     "images" => array(array('url' => $image_link)),
                                     "fields" => array(
                                                        array(
                                                              'name' => "weight",
                                                              'value' => $weight
                                                              ),
                                                        array(
                                                              'name' => "description",
                                                              'value' => $description
                                                              ),
                                                        array(
                                                              'name' => "brand",
                                                              'value' => $brand
                                                              ),
                                                        array(
                                                              'name' => "country_of_shipments",
                                                              'value' => 'Australia'
                                                              ),
                                                        array(
                                                              'name' => "vat",
                                                              'value' => $taxRate
                                                              ),
                                                        array(
                                                              'name' => "barcode",
                                                              'value' => $barcode
                                                              )
                                                      )
                                      );
               //fwrite($fpPO, "\n\n". json_encode($product_row));
            }
      }
      $data = json_encode($product_row);
      if(!empty(json_decode($data, true))){
        echo "\nUploading to Azoya...\n";
        include(BP.'/app/code/Project/Newproducts/Azoya/productCreate.php');
      }
foreach ($product_row as $file) {
    $result = [];
    array_walk_recursive($file, function($item) use (&$result) {
        $result[] = $item;
    });
    fputcsv($fpPO, $result);
}
    }
    fclose($fpPO);
    echo "\n\n---------------------------------------------------------\n";
    echo "Process Completed...";
    echo "\n---------------------------------------------------------\n";

  }

    //Use to strip text
    private function addThis($text)
    {
     $text = strip_tags($text);
     $text = str_replace(array("\r\n", "\r", "\n"), ' ', $text);
     $text = html_entity_decode($text,ENT_QUOTES);
     $text = str_replace('"','',$text);
     return $text;
    }
}
