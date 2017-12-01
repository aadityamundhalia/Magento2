<?php
namespace Project\OrderReportES\Model\Mail;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @param Api\AttachmentInterface $attachment
     */
    public function addAttachment($string, $filename)
    {
        $this->message->createAttachment(
            $string,
            \Zend_Mime::TYPE_OCTETSTREAM,
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $filename
        );
        return $this;
     }
  }
