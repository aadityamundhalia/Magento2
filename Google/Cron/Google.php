<?php
namespace Project\Google\Cron;

class Google {
   protected $_logger;
   protected $_map;
   protected $_productCollectionFactory;
   protected $_storeManager;
   protected $_stock;
   protected $_resourceCon;
   protected $_categoryRepository;
   protected $_categoryFactory;
   protected $_catagory;
   protected $_filesystem;
   protected $_file;
   protected $_helper;
   protected $_store;

   public function __construct(
       \Psr\Log\LoggerInterface $logger,
       \Project\Google\Model\Map $map,
       \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
       \Magento\Store\Model\StoreManagerInterface $storeManager,
       \Magento\CatalogInventory\Api\StockRegistryInterface $stock,
       \Magento\Framework\App\ResourceConnection $resourceCon,
       \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
       \Magento\Catalog\Model\CategoryFactory $categoryFactory,
       \Magento\Catalog\Model\Category $catagory,
       \Magento\Framework\Filesystem\DirectoryList $directoryList,
       \Magento\Framework\Filesystem\Io\File $file,
       \Project\Google\Helper\Data $helper,
       \Magento\Store\Model\StoreRepository $store
   )
    {
     $this->_logger = $logger;
     $this->_map = $map;
     $this->_productCollectionFactory = $productCollectionFactory;
     $this->_storeManager = $storeManager;
     $this->_stock = $stock;
     $this->_resourceCon = $resourceCon;
     $this->_categoryRepository = $categoryRepository;
     $this->_categoryFactory = $categoryFactory;
     $this->_catagory = $catagory;
     $this->_filesystem = $directoryList;
     $this->_file = $file;
     $this->_helper = $helper;
     $this->_store = $store;
    }

   public function execute()
   {
     	$this->_logger->debug('Google cron ran successfully');
      $connection = $this->_resourceCon->getConnection();
      $tableName = $this->_resourceCon->getTableName('ewave_aa_options'); //gives table name with prefix

      $storeArray = array();
      $stores = $this->_store->getlist();
      foreach ($stores as $store)
      {
           $storeId = $store["store_id"];
           $storeName = $store["name"];
           $storeArray[] = array('id' => $storeId, 'name' => $storeName);
      }
      $helper = $this->_helper->getStoreConfig('store');
      $storeViewArray = explode(',', $helper);
      $storeViewArraySize = count($storeViewArray);
      for ($a=0; $a < $storeViewArraySize; $a++) {

          $storeId = $this->searchForStore($storeViewArray[$a], $storeArray);
          if($storeId == 6)
          {
              $storeCode = 'po';
          }

          if ($storeId == 5)
          {
              $storeCode = 'cd';
          }
          $filename = 'shoppingFeed_'.$storeCode.'.xml';
          $filePath = $this->_filesystem->getPath('media') . '/GoogleFeed/';
          $path = $filePath . $filename;
          if (!is_dir($filePath)) {
              $ioAdapter = $this->_file;
              $ioAdapter->mkdir($filePath, 0775);
          }
          $fp = fopen($filePath . $filename, 'w');

          $store = $this->_storeManager->getStore($storeId)->getBaseUrl();
          $mappings = $this->_map->getCollection()->getData();

          $mediaPath = BP.'/app/code/Project/Google/Setup/data/file.csv';
          $MapFile = fopen($mediaPath, 'r');
          $balckSku = array();
          $myLine = fgetcsv($MapFile);

          $roundlimit = 500;

          $max = $this->_productCollectionFactory->create()
                                                 ->addAttributeToSelect('*')
                                                 ->setStoreId($storeId)
                                                 ->addAttributeToFilter('sku', array('nin' => $myLine))
                                                 ->addAttributeToFilter('prescription', 0)
                                                 ->addAttributeToFilter('chemist', 0);

          $max = $max->getSize();
          $maxRound = round($max/$roundlimit);
          if($maxRound < $max){
            $maxRound = $maxRound+1;
          }

          echo "\nTotal: ".$max."\n";

          echo "\nSelected Store: ".$storeId."\n";

          for ($i=1; $i <= $maxRound; $i++) {
            echo "\n\nRunning batch: ".$i."/".$maxRound."\n";
            $feed_data = array();
            $collections =  $this->_productCollectionFactory->create()
                                                            ->addAttributeToSelect('*')
                                                            ->setStoreId($storeId)
                                                            ->addAttributeToFilter('sku', array('nin' => $myLine))
                                                            ->addAttributeToFilter('prescription', 0)
                                                            ->addAttributeToFilter('chemist', 0)
                                                            ->setPage($i, $roundlimit)
                                                            ->load();
            foreach ($collections as $item) {
                echo ".";
                $product = new \stdClass();
                try
                {
                    $product->id = $item->getId();
                    $product->title = $item->getName();
                    $product->link = $store.$item->getUrlKey();
                    $product->price = round($item->getPrice(),2). " AUD";
                    $product->description = $item->getDescription();
                    $product->condition = "new";
                    $product->image_link = rtrim($store,"/").$item->getImage();
                    $product->availability = "in stock";
                    $product->brand = $item->getBrand();
                    if(!is_null($product->brand))
                    {
                      $sql = "Select option_id, meta_title FROM ".$tableName." where option_id = ".$product->brand." limit 1";
                      $result = $connection->fetchAll($sql);
                      $product->brand = $result[0]['meta_title'];
                    }
                    if(!is_null($item->getGtin()) || $item->getGtin() == "")
                    {
                        $product->gtin = $item->getGtin();
                    }
                    else
                    {
                        $product->gtin = $item->getBarcode();
                    }
                    $product->mpn = $item->getEntityId();

                    $catagotyPath = "";
                    $size = sizeof($item->getCategoryIds());
                    for ($j=0; $j < $size; $j++) {
                      $product_type = $this->_catagory->load($item->getCategoryIds()[$j]);
                      if($j > 0)
                      {
                          $catagotyPath .= " > ".$product_type->getName();
                      }
                      else {
                        $catagotyPath .= $product_type->getName();
                      }
                    }
                    $product->product_type = $catagotyPath;
                    $product->adwords_redirect = $store.$item->getUrlKey();
                    $product->shipping_weight = $item->getWeight() . " g";
                    $last = $size - 1;
                    $catId = $item->getCategoryIds()[$last];
                    foreach ($mappings as $mapping)
                    {
                      if($catId == $mapping['catagory_id'])
                      {
                        $product->google_product_category = $mapping['google_catagory'];
                      }
                    }
                }
                catch (\Exception $exc)
                {
                    //Left empty
                    continue;
                }
                array_push($feed_data, $product);
            }
            $xml_feed = self::generate_xml_feed($feed_data, $store);
            fwrite($fp, $xml_feed);
          }
          fclose($fp);
        }
        echo "\n";
    }

    private function generate_xml_feed($feed_data, $store)
    {
        $xmlWriter = new \XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString("    ");
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->startElement('rss');
        $xmlWriter->writeAttribute('version', '2.0');
        $xmlWriter->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $xmlWriter->startElement('channel');
        $xmlWriter->startElement('title');
        $xmlWriter->writeCData("Data feed Title");
        $xmlWriter->endElement();
        $xmlWriter->startElement('link');
        $xmlWriter->writeCData($store);
        $xmlWriter->endElement();
        $xmlWriter->startElement('description');
        $xmlWriter->writeCData("Data feed description.");
        $xmlWriter->endElement();
        foreach ($feed_data as $row) {
            $xmlWriter->startElement('item');
            foreach ($row as $key => $value) {
                $xmlWriter->startElement('g:'.$key);
                in_array($key, ['title', 'description']) ? $xmlWriter->writeCData($value) : $xmlWriter->text($value);
                $xmlWriter->endElement(); //g:KEY
            }
            $xmlWriter->endElement(); //item
        }
        $xmlWriter->endElement(); //channel
        $xmlWriter->endElement(); //rss
        return $xmlWriter->flush(true);
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
