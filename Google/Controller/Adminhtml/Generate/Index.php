<?php
namespace Project\Google\Controller\Adminhtml\Generate;

class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
      $this->_view->loadLayout();
      $this->_view->renderLayout();
    }
}
