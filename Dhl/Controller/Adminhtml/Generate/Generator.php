<?php
namespace Project\Dhl\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

class Generator extends \Magento\Backend\App\Action
{
    protected $_filesystem;
    protected $_file;
    protected $_resultPageFactory;
    protected $_coreRegistry;
    private $redirectFactory;

    public function __construct(
        Context $context,
        File $file,
        PageFactory $pageFactory,
        Registry $coreRegistry,
        DirectoryList $directoryList,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        $this->_filesystem = $directoryList;
        $this->_file = $file;
        $this->_resultPageFactory = $pageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->redirectFactory = $redirectFactory;
        parent::__construct($context);
    }

    public function execute()
    {
      $cmd = "/usr/bin/php70 ".BP."/bin/magento project:generate";
      $output = shell_exec($cmd);
      echo "<pre>$output</pre>";
      return $this->resultRedirectFactory->create()
                  ->setPath('dhl/generate/index');
    }
}
