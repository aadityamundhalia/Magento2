<?php
namespace Project\Dhl\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

class Index extends \Magento\Backend\App\Action
{
    protected $_filesystem;
    protected $_file;
    protected $_resultPageFactory;
    protected $_coreRegistry;

    public function __construct(
        Context $context,
        File $file,
        PageFactory $pageFactory,
        Registry $coreRegistry,
        DirectoryList $directoryList
    ) {
        $this->_filesystem = $directoryList;
        $this->_file = $file;
        $this->_resultPageFactory = $pageFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {
/*
        $cmd = "php ".BP."/bin/magento project:generate";
        while (@ ob_end_flush()); // end all output buffers if any
        $proc = popen($cmd, 'r');
        echo '<div class="hidden"><pre>';
        while (!feof($proc))
        {
            echo fread($proc, 4096);
            @ flush();
        }
        echo '</pre></div>';
*/
        $list = array();
        foreach (glob($this->_filesystem->getPath('media') . '/DHL/*.txt') as $f) {
          $list[filemtime($f)] = $f;
        }
        krsort($list);
        $resultPage = $this->_resultPageFactory->create();
        $this->_coreRegistry->register('fileList', $list);
        return $resultPage;
    }
}
