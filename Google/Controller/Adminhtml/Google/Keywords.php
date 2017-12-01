<?php
namespace Project\Google\Controller\Adminhtml\Google;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Project\Google\Model\Blacklist;
use Project\Google\Model\Google;

class Keywords extends \Magento\Backend\App\Action
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
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
      $keywords = $this->_objectManager->create('Project\Google\Model\Google')
                                                 ->getCollection()
                                                 //->setPageSize(20)
                                                 //->setCurPage(20)
                                                 ->getData();
      echo json_encode($keywords);
    }
}
