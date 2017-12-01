<?php

namespace Project\OrderReportES\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Area;
//use Magento\Framework\Mail\Template\TransportBuilder;
use Project\OrderReportES\Model\Mail\TransportBuilder;

class Email extends Data {

    //construct params
    protected $_transportBuilder;

    public function __construct(Context $context, TransportBuilder $transportBuilder) {
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context);
    }

    public function sendEmail($file, $filename, $storeid, $tomail, $bcc, $toname) {

        $templateId = $this->getConfig('template');
        $identity = $this->getConfig('identity');
        $vars = array();

        if ($templateId && $identity) {
            $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeid])
                    ->setTemplateVars($vars)
                    ->setFrom($identity)
                    ->addTo($tomail, $toname)
                    ->addBcc($bcc)
                    ->addAttachment(file_get_contents($file), $filename)
                    ->getTransport();
            $transport->sendMessage();
        }

        return $this;
    }
    public function test()
    {
      return $this->getConfig('identity');
    }
}
