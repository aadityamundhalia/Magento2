<?php

namespace Project\Google\Model\ResourceModel\Google;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Project\Google\Model\Google', 'Project\Google\Model\ResourceModel\Google');
    }
}
