<?php
namespace Project\Google\Block;

use Magento\Framework\App\Action\Context;

class Keywords extends \Magento\Framework\View\Element\Template
{
    public function getPath()
    {
      $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
      return $path;
    }
}
