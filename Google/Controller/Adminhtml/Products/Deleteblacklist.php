<?php
namespace Project\Google\Controller\Adminhtml\Products;

use Magento\Backend\App\Action\Context;
use Project\Google\Model\Blacklist;
use Project\Google\Model\Google;

class Deleteblacklist extends \Magento\Backend\App\Action
{
    protected $_googleFactory;
    protected $_blacklistFactory;

    public function __construct(Context $context,
                                Google $googleFactory,
                                Blacklist $blacklistFactory
                               )
    {
        $this->_googleFactory = $googleFactory;
        $this->_blacklistFactory = $blacklistFactory;
        parent::__construct($context);
    }

    public function execute()
    {
      $post = $this->getRequest()->getPostValue();

      $item = $post['item'];
      $blacklist = $this->_objectManager->get('Project\Google\Model\Blacklist')
                                                 ->getCollection()
                                                 ->addFieldToFilter('blacklist_id', $item)
                                                 ->getData();

      if(!isset($blacklist[0]['name']))
      {
        $message = array('message' => 'Do not exists');
        $result = json_encode($message);
      }
      else {
        try {
                $delete = $this->_objectManager->create('Project\Google\Model\Blacklist')->load($blacklist[0]['blacklist_id']);
                $delete = $delete->delete($blacklist[0]['blacklist_id']);
                $message = array('message' => 'Success');
                $result = json_encode($message);
            } catch (Exception $e) {
              $message = array('message' => $e->getMessage());
              $result =  json_encode($message);
            }
      }
      echo $result;
    }
}
