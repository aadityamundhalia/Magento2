<?php
namespace Project\Dhl\Controller\Adminhtml\Clean;

use Magento\Backend\App\Action\Context;
use Project\Dhl\Model\Zdhl;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Clean extends \Magento\Backend\App\Action
{
    protected $_zdhlFactory;
    protected $_storeManager;
    protected $_order;
    protected $_countryFactory;
    protected $_coreRegistry;
    protected $_resultPageFactory;

    public function __construct(Context $context,
                                Zdhl $zdhlFactory,
                                StoreManagerInterface $storeManager,
                                OrderInterface $order,
                                CountryFactory $countryFactory,
                                Registry $coreRegistry,
                                PageFactory $pageFactory
                               )
    {
        $this->_zdhlFactory = $zdhlFactory;
        $this->_storeManager = $storeManager;
        $this->_order = $order;
        $this->_countryFactory = $countryFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
      $connection = $this->_zdhlFactory->getResource()->getConnection();
      $tableName = $this->_zdhlFactory->getResource()->getMainTable();
      $connection->truncateTable($tableName);
      $resultPage = $this->_resultPageFactory->create();
      return $resultPage;
    }
}
