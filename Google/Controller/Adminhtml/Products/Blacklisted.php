<?php
namespace Project\Google\Controller\Adminhtml\Products;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Project\Google\Model\Blacklist;
use Project\Google\Model\Google;

class Blacklisted extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_googleFactory;
    protected $_blacklistFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Google $googleFactory,
        Blacklist $blacklistFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
      $blacklisted = $this->_objectManager->create('Project\Google\Model\Blacklist')
                                                 ->getCollection()
                                                 ->getData();
      echo json_encode($blacklisted);
    }
}
