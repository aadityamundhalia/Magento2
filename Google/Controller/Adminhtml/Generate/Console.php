<?php
namespace Project\Google\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\DirectoryList;

class Console extends \Magento\Backend\App\Action
{
    protected $_filesystem;
    protected $_file;

    public function __construct(
        Context $context,
        File $file,
        DirectoryList $directoryList
    ) {
        $this->_filesystem = $directoryList;
        $this->_file = $file;
        parent::__construct($context);
    }

    public function execute()
    {

      $cmd = "/usr/bin/php70 ".BP."/bin/magento project:balckList";
      $output = shell_exec($cmd);
      echo "<pre>$output</pre>";

      return $this->resultRedirectFactory->create()
                  ->setPath('keywords/products/index');
    }
}
