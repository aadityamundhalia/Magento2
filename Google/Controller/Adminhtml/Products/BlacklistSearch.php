<?php
namespace Project\Google\Controller\Adminhtml\Products;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Project\Google\Model\Blacklist;
use Project\Google\Model\Google;

class BlacklistSearch extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_googleFactory;
    protected $_blacklistFactory;
    protected $_productCollectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Google $googleFactory,
        Blacklist $blacklistFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
      $post = $this->getRequest()->getPostValue();
      if(!empty($post['blacklist']))
      {
        $search = $post['blacklist'];;
        $collection = $this->_productCollectionFactory
                           ->create()
                           ->addAttributeToFilter('name', array('like' => '%'.$search.'%'))
                           ->setPageSize(4)
                           ->addAttributeToSelect('*');
        echo json_encode($collection->getData());
      }else {
        $empty = array('name' => 'Please enter a value');
        echo json_encode($empty);
      }
    }
}
