<?php

namespace Project\OrderReportES\Helper;

class MyHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_stockItemRepository;
    protected $_scopeConfig;
    protected $_storeScope;

    public function __construct(
          \Magento\CatalogInventory\Model\Stock\StockItemRepository $_stockItemRepository,
          \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        )
    {
        $this->_stockItemRepository = $_stockItemRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    /**
     * @param $ids -> Product Ids to check stock.
     * @return array -> Return the product is in the correct order.
     */
    public function getGeneralConfig($field){
        $result = $this->_scopeConfig->getValue('order/general/'.$field, $this->_storeScope);
        return $result;
    }
}
