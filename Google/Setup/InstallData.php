<?php
namespace Project\Google\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallData implements InstallDataInterface
{
    protected $_googleFactory;
    protected $_map;

    public function __construct(\Project\Google\Model\Google $googleFactory,
                                \Project\Google\Model\Map $map
                                )
    {
        $this->_googleFactory = $googleFactory;
        $this->_map = $map;
    }


    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $mediaPath = BP.'/app/code/Project/Google/Setup/data/googleBlacklist.csv';
        $file = fopen($mediaPath, 'r');
        $items = array();
        while (($line = fgetcsv($file)) !== FALSE) {
          $items[] = array("name" => $line[0]);
        }
        fclose($file);
        foreach ($items as $item) {
          $keyword = $this->_googleFactory->setData($item)->save();
        }

        $mapMediaPath = BP.'/app/code/Project/Google/Setup/data/map.csv';
        $MapFile = fopen($mapMediaPath, 'r');
        $myItems = array();
        while (($myLine = fgetcsv($MapFile)) !== FALSE) {
          $mapItems[] = array("catagory_id" => $myLine[0], "google_catagory" => $myLine[1]);
        }
        fclose($MapFile);
        foreach ($mapItems as $mapItem) {
          $keyword = $this->_map->setData($mapItem)->save();
        }
    }

}
