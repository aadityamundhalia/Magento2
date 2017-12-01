<?php
namespace Project\Google\Controller\Adminhtml\Google;

use Magento\Backend\App\Action\Context;
use Project\Google\Model\Blacklist;
use Project\Google\Model\Google;

class Addkeyword extends \Magento\Backend\App\Action
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
      //echo json_encode($post);
      $item = $post['item'];
      $keywords = $this->_objectManager->get('Project\Google\Model\Google')
                                                 ->getCollection()
                                                 ->addFieldToFilter('name', $item)
                                                 ->getData();
      if(!isset($keywords[0]['name']))
      {
        try {
                $add = $this->_googleFactory->setName($item)->save();
                $message = array('message' => 'Success');
                $result = json_encode($message);
            } catch (Exception $e) {
                $message = array('message' => $e->getMessage());
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
