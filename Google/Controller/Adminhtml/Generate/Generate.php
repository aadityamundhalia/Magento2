<?php
namespace Project\Google\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Project\Google\Model\Blacklist;
use Project\Google\Model\Google;

class Generate extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_googleFactory;
    protected $_blacklistFactory;
    protected $_productCollectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Google $googleFactory,
        Blacklist $blacklistFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        parent::__construct($context);
        $this->_blacklistFactory = $blacklistFactory;
        $this->_googleFactory = $googleFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
      try {
            $path = BP.'/app/code/Project/Google/Setup/data/file.csv';
            $fp = fopen($path, 'w');
            fclose($fp);
            $keywords = $this->_objectManager->create('Project\Google\Model\Google')
                                                       ->getCollection()
                                                       ->getData();
            foreach ($keywords as $keyword)
            {
              $search = $keyword['name'];
              $collection = $this->_productCollectionFactory
                                 ->create()
                                 ->addAttributeToFilter('name', array('like' => '%'.$search.'%'))
                                 ->addAttributeToSelect('sku')
                                 ->getData();
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
              }
            }
            $this->blacklist();
      } catch (Exception $e) {
        $result = array('message' => $e->getMessage());
      }
      echo json_encode($result);
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
      $blacklisted = $this->_objectManager->create('Project\Google\Model\Blacklist')
                                                 ->getCollection()
                                                 ->getData();
      $black = array();
      foreach ($blacklisted as $blacklist) {
        $black[] = $blacklist['sku'];
      }
      $new = array_merge($list, $black);
      $final = array_unique($new);
      $fp = fopen($path, 'w');
      fputcsv($fp, $final);
      fclose($fp);
      //echo json_encode($blacklisted[0]);
    }
}
