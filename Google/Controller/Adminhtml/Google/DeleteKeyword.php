<?php
namespace Project\Google\Controller\Adminhtml\Google;

use Magento\Backend\App\Action\Context;
use Project\Google\Model\Blacklist;
use Project\Google\Model\Google;

class DeleteKeyword extends \Magento\Backend\App\Action
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
      $keywords = $this->_objectManager->get('Project\Google\Model\Google')
                                                 ->getCollection()
                                                 ->addFieldToFilter('keyword_id', $item)
                                                 ->getData();

      if(!isset($keywords[0]['name']))
      {
        $message = array('message' => 'Do not exists');
        $result = json_encode($message);
      }
      else {
        try {
                $delete = $this->_objectManager->create('Project\Google\Model\Google')->load($keywords[0]['keyword_id']);
                $delete = $delete->delete($keywords[0]['keyword_id']);
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
