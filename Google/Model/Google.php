<?php

namespace Project\Google\Model;

use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;

/**
 * Contact Model
 *
 * @author      Pierre FAY
 */
class Google extends AbstractModel
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Project\Google\Model\ResourceModel\Google::class);
    }
}
