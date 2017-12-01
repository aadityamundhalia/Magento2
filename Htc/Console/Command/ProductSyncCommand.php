<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Project\Htc\Console\Command;


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
/**
 * An Abstract class for Indexer related commands.
 */
class ProductSyncCommand extends Command
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
    protected $_helper;
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
                                \Project\Htc\Helper\Data $helper,
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
      $this->_helper = $helper;
      $this->_urlInterface = $urlInterface;
      $this->_resourceCon = $resourceCon;
      $this->_state = $state;

      parent::__construct();
    }

    protected function configure()
    {
        $this->setName('project:Htc')->setDescription('Azoya TOP500 products api call');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
          $this->_state->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
        // Intentionally left empty.
        }
        $connection = $this->_resourceCon->getConnection();
        $tableName = $this->_resourceCon->getTableName('ewave_aa_options'); //gives table name with prefix
        echo "Initiating process...\n";
        $filename = date("Ymdhisa")."PO"; //set filename
        //echo $filename . "<br>";
        $top200 = BP.'/app/code/Project/Htc/Azoya/TOP500.txt';
        $t200 = fopen ($top200, 'r');
        $storeArray = array();
        $stores = $this->_store->getlist();
        foreach ($stores as $store)
        {
             $storeId = $store["store_id"];
             $storeName = $store["name"];
             $storeArray[] = array('id' => $storeId, 'name' => $storeName);
             //echo "StoreName:" . $storeName . " | Storeid: " . $storeId . "\n";
        }
        //print_r($storeArray);
        $helper = $this->_helper->getStoreConfig('store');
        $storeViewArray = explode(',', $helper);
        //print_r($storeViewArray);

         //echo "<br>";
         echo "\nGetting TOP500 file...\n";
         while(($data = fgetcsv($t200,1000,"\t")) !== FALSE)
         {
            $t200sku[] = trim($data[1]);
         }

         $first = date('Y-m-d',mktime(0,0,0,date("m"),date("d")-4, date("Y"))); //date 4 days ago

         $filePath = $this->_filesystem->getPath('log') . '/china/';
         if (!is_dir($filePath)) {
             $ioAdapter = $this->_file;
             $ioAdapter->mkdir($filePath, 0775);
         }
         echo "Starting to generate...\n";

         $roundlimit = 500;

         $max =  $this->_productCollectionFactory->create()
                                                         ->addAttributeToSelect('*')
                                                         ->setStoreId($storeId)
                                                         ->addAttributeToFilter('prescription', 0)
                                                         ->addAttributeToFilter('chemist', 0)
                                                         ->addAttributeToFilter('updated_at', array('gteq' =>$first))
                                                         ->addAttributeToFilter('flammable', 0)
                                                         ->addAttributeToFilter('refrigerated', 0);
                                                         //->load();
         $max = $max->getSize();
         $maxRound = round($max/$roundlimit);
         if($maxRound < $max){
           $maxRound = $maxRound+1;
         }
         $filePath = $filePath . $filename;
         fopen($filePath, 'ab+');

         echo "\nTotal: ".$max."\n";

         $storeViewArraySize = count($storeViewArray);
         for ($a=0; $a < $storeViewArraySize; $a++) {

           $storeId = $this->searchForStore($storeViewArray[$a], $storeArray);
           echo "\nSelected Store: ".$storeId."\n";

           for ($i=1; $i <= $maxRound; $i++) {
             $arrItem = array();
             echo "\n\nRunning batch: ".$i."/".$maxRound."\n";
             $products = $this->_productCollectionFactory->create()
                                                         ->addAttributeToSelect('*')
                                                         ->setStoreId($storeId)
                                                         ->addAttributeToFilter('prescription', 0)
                                                         ->addAttributeToFilter('chemist', 0)
                                                         ->addAttributeToFilter('updated_at', array('gteq' =>$first))
                                                         ->addAttributeToFilter('flammable', 0)
                                                         ->addAttributeToFilter('refrigerated', 0)
                                                         ->setPage($i, $roundlimit)
                                                         ->load();
            foreach ($products as $product) {
              //Set Variables
              //echo "<pre>";print_r($product->getData());echo "</pre>";
              $sku = $product->getSku();
              $name = $this->addThis($product->getName());
              $price = $product->getChinaPrice();
              $specialPrice = $product->getSpecialPrice();
              $qty = $this->_stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
              $stockStatus = $this->_stock->getStockItem($product->getId())->getIsInStock();
              $status = $product->getStatus();
              $image = $product->getImage();
              $taxRate = $product->getTaxClassId()=='2'?0.1:0;
              $rrp = $product->getMsrp();
              $ebayInven = $product->getEbayInventory();
              $flammable=$product->getFlammable();
              $weight = $product->getWeight();
              if($weight == null)
              {
                  $weight = "";
              }
              $description = $this->addThis($product->getDescription());
              $brand = $product->getBrand();
              if(!is_null($brand))
              {
                $sql = "Select option_id, meta_title FROM ".$tableName." where option_id = ".$brand." limit 1";
                $result = $connection->fetchAll($sql);
                if(!empty($result))
                {
                  if(isset($result[0]['meta_title']))
                  {
                      $brand = $result[0]['meta_title'];
                  }
                  else {
                    $brand = "";
                  }
                }
                else {
                  $brand = "";
                }
              }
              $barcode = $product->getBarcode();
              //echo "<pre>";print_r($stockStatus->getData());echo "</pre>";

              //Set Price
              if(is_null($price) || $price==''){
                $price = $product->getFinalPrice();
              }
              $price_num = $price;
              if($price_num==0)
              {
                continue;
              }
              if($rrp==0 or is_null($rrp))
               {
                $rrp=$price_num;
              }
              //Price set

              //Set inventory
              if(is_null($ebayInven)){
                $ebayInven=0;
              }elseif($ebayInven<0){
                $ebayInven=0;
              }
              if($qty>999)
              {
                $qtyOnApi=1000;
              }
              elseif($qty>50)
              {
                $qtyOnApi = $ebayInven;
              } else {
                $qtyOnApi=0;
              }
              //Inventory set

              //Search Sku from List
              if(array_search($sku, $t200sku)){
                $qtyOnApi=$ebayInven;
              }
              //Sku search end

              //
              if($stockStatus==1 and $flammable=='1'){
                $stockStatus = 0;
              }
              if($qtyOnApi<=0){
                $stockStatus = 0;
              }

              if($price_num > $rrp)
              {
                $rrp=$price_num;
              }

              $image_link =  $this->_storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'.$image;
              //echo "\n".$image_link."\n";
              $arrItem[] = array(
                                  'sku'=>$sku,
                                  'name'=>$name,
                                  'price'=>$rrp,
                                  'special_price'=>$price_num,
                                  'currency_code'=>"AUD",
                                  'qty'=>$qtyOnApi,
                                  'is_in_stock'=>$stockStatus,
                                  'image'=> array(array('url' => $image_link)),
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
              echo ".";
            }
            echo "\nUploading to Azoya...\n";
            //$arrJSON = array('items'=>$arrItem);
            $plaintext = json_encode($arrItem);
            if(!empty(json_decode($plaintext, true))){
              $providerCode = "po";
              if($storeId == 5)
              {
                $providerCode = "cd";
              }
              elseif ($storeId == 6) {
                $providerCode = "po";
              }
              else {
                $providerCode = "po";
              }
              include(BP.'/app/code/Project/Htc/Azoya/productSync.php');
              $myfile = fopen($filePath, 'a' );
              fwrite($myfile, "\n". $plaintext);
            }
        }
      }
      if(isset($myfile))
      {
          fclose($myfile);
      }
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

    function searchForStore($name, $array)
    {
       foreach ($array as $key => $val) {
           if ($val['name'] === $name) {
               return $val['id'];
           }
       }
       return null;
    }
}
