<?php
namespace Project\Google\Controller\Adminhtml\Google;

class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
      $this->_view->loadLayout();
      $this->_view->renderLayout();
    }
}
