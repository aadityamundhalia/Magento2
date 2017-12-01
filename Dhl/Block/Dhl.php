<?php
namespace Project\Dhl\Block;
use Magento\Framework\View\Element\Template;
use Project\Dhl\Model\Zdhl;

class Dhl extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry;
    protected $_zdhlFactory;
    protected $_formKey;
    protected $_urlInterface;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Zdhl $zdhlFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey $formKey,
        //\Magento\Framework\UrlInterface $urlInterface,
        //\Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_formKey = $formKey;
        $this->_urlInterface = $context->getUrlBuilder();
        //$this->_urlInterface = $urlInterface;
        //$this->_storeManager=$storeManager;
        $this->_storeManager = $context->getStoreManager();
        $this->_zdhlFactory = $zdhlFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }

    public function getShippment()
    {
        return $this->_coreRegistry->registry('barcode');
    }

    public function link($url)
    {
    	$post = $this->_urlInterface->getUrl($url);
      return $post;
    }

    public function getList()
    {
        return $this->_coreRegistry->registry('fileList');
    }
    public function getPath()
    {
      $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
      return $path;
    }
    public function turncateTable()
    {
      $connection = $this->_zdhlFactory->getResource()->getConnection();
      $tableName = $this->_zdhlFactory->getResource()->getMainTable();
      $connection->truncateTable($tableName);
      return "Done";
    }
}
