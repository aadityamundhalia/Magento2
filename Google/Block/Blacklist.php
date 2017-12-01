<?php
namespace Project\Google\Block;
use Magento\Framework\View\Element\Template;

class Blacklist extends \Magento\Framework\View\Element\Template
{
  protected $_coreRegistry;
  protected $_formKey;
  protected $_urlInterface;
  protected $_storeManager;

  public function __construct(
      \Magento\Framework\View\Element\Template\Context $context,
      \Magento\Framework\Registry $coreRegistry,
      \Magento\Framework\Data\Form\FormKey $formKey,
      //\Magento\Framework\UrlInterface $urlInterface,
      //\Magento\Store\Model\StoreManagerInterface $storeManager,
      array $data = []
  ) {
      $this->_coreRegistry = $coreRegistry;
      $this->_formKey = $formKey;
      //$this->_urlInterface = $urlInterface;
      //$this->_storeManager=$storeManager;
      $this->_storeManager = $context->getStoreManager();
      $this->_urlInterface = $context->getUrlBuilder();
      parent::__construct(
          $context,
          $data
      );
  }

    public function getHelloWorldTxt()
    {
        $path = BP.'/app/code/Project/Google/Setup/data/file.csv';
        $row = 1;
        $list = array();
        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                $row++;
                for ($c=0; $c < $num; $c++) {
                    $list[] = $data[$c];
                }
            }
            fclose($handle);
        }
        $list = json_encode($list);
        $list = str_replace('"', "", $list);
        $list = str_replace('[', "", $list);
        $list = str_replace(']', "", $list);
        return $list;
    }

    public function getPath()
    {
      $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
      return $path;
    }
    
    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }
}
