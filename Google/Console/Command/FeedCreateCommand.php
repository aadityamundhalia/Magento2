<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Project\Google\Console\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\{ObjectManager, State};
use Project\Google\Model\Blacklist;
use Project\Google\Model\Google;
use Project\Google\Model\Map;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Store\Model\StoreRepository;
/**
 * An Abstract class for Indexer related commands.
 */
class FeedCreateCommand extends Command
{
    protected $_googleFactory;
    protected $_blacklistFactory;
    protected $_productCollectionFactory;
    protected $_state;
    protected $_map;
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
                                State $state,
                                ProductRepositoryInterface $prepo,
                                Google $googleFactory,
                                Blacklist $blacklistFactory,
                                Map $map,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\CatalogInventory\Api\StockRegistryInterface $stock,
                                \Magento\Framework\App\ResourceConnection $resourceCon,
                                CategoryRepositoryInterface $categoryRepository,
                                \Magento\Catalog\Model\CategoryFactory $categoryFactory,
                                \Magento\Catalog\Model\Category $catagory,
                                DirectoryList $directoryList,
                                File $file,
                                \Project\Google\Helper\Data $helper,
                                StoreRepository $store
                               )
    {
      $this->_blacklistFactory = $blacklistFactory;
      $this->_googleFactory = $googleFactory;
      $this->_productCollectionFactory = $productCollectionFactory;
      $this->_state = $state;
      $this->_map = $map;
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

    parent::__construct();
    }

    protected function configure()
    {
        $this->setName('project:FeedCreate')->setDescription('Create Feed -> google');
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

      $storeArray = array();
      $stores = $this->_store->getlist();
      foreach ($stores as $store)
      {
           $storeId = $store["store_id"];
           $storeName = $store["name"];
           $storeArray[] = array('id' => $storeId, 'name' => $storeName);
           //echo "StoreName:" . $storeName . " | Storeid: " . $storeId . "\n";
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
