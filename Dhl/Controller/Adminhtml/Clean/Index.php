<?php
namespace Project\Dhl\Controller\Adminhtml\Clean;

class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
      $this->_view->loadLayout();
      $this->_view->renderLayout();
    }
}
