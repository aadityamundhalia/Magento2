<?php

namespace Project\Dhl\Model\ResourceModel\Zdhl;

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
        $this->_init('Project\Dhl\Model\Zdhl', 'Project\Dhl\Model\ResourceModel\Zdhl');
    }
}
