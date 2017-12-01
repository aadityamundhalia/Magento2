<?php
namespace Project\Google\Block;

use Magento\Framework\App\Action\Context;

class Generate extends \Magento\Framework\View\Element\Template
{
  protected $_coreRegistry;
  protected $_formKey;
  protected $_urlInterface;
  protected $_storeManager;

  public function __construct(
      \Magento\Framework\View\Element\Template\Context $context,
      \Magento\Framework\Registry $coreRegistry,
      \Magento\Framework\Data\Form\FormKey $formKey,
      //\Magento\Framework\UrlInterface $urlInterface,
      //\Magento\Store\Model\StoreManagerInterface $storeManager,
      array $data = []
  ) {
      $this->_coreRegistry = $coreRegistry;
      $this->_formKey = $formKey;
      $this->_storeManager = $context->getStoreManager();
      $this->_urlInterface = $context->getUrlBuilder();
      parent::__construct(
          $context,
          $data
      );
  }

    public function getHelloWorldTxt()
    {
        return 'Hello world!';
    }

    public function getPath()
    {
      $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
      return $path;
    }
}
