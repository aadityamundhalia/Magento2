<?php
namespace Project\Google\Controller\Adminhtml\Products;

use Magento\Backend\App\Action\Context;
use Project\Google\Model\Blacklist;
use Project\Google\Model\Google;

class Addblacklist extends \Magento\Backend\App\Action
{
    protected $_googleFactory;
    protected $_blacklistFactory;
    protected $_productCollectionFactory;

    public function __construct(Context $context,
                                Google $googleFactory,
                                Blacklist $blacklistFactory,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
                               )
    {
        $this->_googleFactory = $googleFactory;
        $this->_blacklistFactory = $blacklistFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
      $post = $this->getRequest()->getPostValue();
      $item = $post['item'];
      $blacklisted = $this->_objectManager->get('Project\Google\Model\Blacklist')
                                                 ->getCollection()
                                                 ->addFieldToFilter('name', $item)
                                                 ->getData();
      if(!isset($blacklisted[0]['name']))
      {
        $collection = $this->_productCollectionFactory
                           ->create()
                           ->addAttributeToFilter('name', $item)
                           ->addAttributeToSelect('*')
                           ->getData();
        if(!empty($collection[0]['sku']) && !empty($collection[0]['name']))
        {
          try {
            $data = [
                        'sku' => $collection[0]['sku'],
                        'name' => $collection[0]['name']
                    ];
                    $add = $this->_blacklistFactory;
                    $add->addData($data)->save();

                  $message = array('message' => 'Success');
                  $result = json_encode($message);
              } catch (Exception $e) {
                  $message = array('message' => $e->getMessage());
                  $result =  json_encode($message);
              }
        }
        else {
          $message = array('message' => 'sku not found');
          $result =  json_encode($message);
        }
      }
      else {
        $message = array('message' => 'already exists');
        $result =  json_encode($message);
      }
      echo $result;
    }
}
