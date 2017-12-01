<?php

namespace Project\Google\Model\ResourceModel\Map;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Contact Resource Model Collection
 *
 * @author      Pierre FAY
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Project\Google\Model\Map', 'Project\Google\Model\ResourceModel\Map');
    }
}
