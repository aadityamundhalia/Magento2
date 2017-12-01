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
/**
 * An Abstract class for Indexer related commands.
 */
class BlackListCommand extends Command
{
    protected $_googleFactory;
    protected $_blacklistFactory;
    protected $_productCollectionFactory;

    public function __construct(
                                State $state,
                                ProductRepositoryInterface $prepo,
                                Google $googleFactory,
                                Blacklist $blacklistFactory,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
                               )
    {
      $this->_blacklistFactory = $blacklistFactory;
      $this->_googleFactory = $googleFactory;
      $this->_productCollectionFactory = $productCollectionFactory;
      try {
      //$state->setAreaCode('adminhtml');
      } catch (\Magento\Framework\Exception\LocalizedException $e) {
      // Intentionally left empty.
      }

    parent::__construct();
    }

    protected function configure()
    {
        $this->setName('project:balckList')->setDescription('Generate Blacklisted Sku -> google');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $path = BP.'/app/code/Project/Google/Setup/data/file.csv';
      $fp = fopen($path, 'w');
      fclose($fp);
      $keywords = $this->_googleFactory->getCollection()->getData();
      echo "Fetching...";
      echo "\n";
      foreach ($keywords as $keyword)
      {
        $search = $keyword['name'];
        $collection = $this->_productCollectionFactory
                           ->create()
                           ->addAttributeToFilter('name', array('like' => '%'.$search.'%'))
                           ->addAttributeToFilter('description', array('like' => '%'.$search.'%'))
                           ->addAttributeToSelect('sku')
                           ->getData();
        echo "Searching for ". $search . "...\n";
        if(!empty($collection))
        {
          $fp = fopen($path, 'a');
          foreach ($collection as $collection)
          {
            $sku = array('sku' => $collection['sku']);
              fputcsv($fp, $sku);
          }
          fclose($fp);
          $result = array('message' => 'Success');
          echo "Found " . $search . " in list\n";
        }
      }
      $this->blacklist();
    }

    public function blacklist()
    {
      $path = BP.'/app/code/Project/Google/Setup/data/file.csv';
      $row = 1;
      $list = array();
      if (($handle = fopen($path, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
              $num = count($data);
              $row++;
              for ($c=0; $c < $num; $c++) {
                  $list[] = $data[$c];
              }
          }
          fclose($handle);
      }
      $blacklisted = $this->_googleFactory->getCollection()
                                          ->getData();
      $black = array();
        foreach ($blacklisted as $blacklist) {
          if(isset($blacklist['sku']))
          {
            $black[] = $blacklist['sku'];
          }
          else {
            $black[] = "empty";
          }
        }
        $new = array_merge($list, $black);
        $final = array_unique($new);
        $fp = fopen($path, 'w');
        fputcsv($fp, $final);
        fclose($fp);
    }

}
