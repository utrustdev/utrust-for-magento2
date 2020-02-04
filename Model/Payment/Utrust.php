<?php

namespace Utrust\Payment\Model\Payment;

class Utrust extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = "utrust";
    protected $_isOffline = true;

    protected $_infoBlockType = \Utrust\Payment\Block\Info::class;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }

    /**
     * (Override) Get title text from config
     *
     * @return string
     */
    public function getTitle()
    {
        return trim($this->getConfigData('frontend/title'));
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('frontend/instructions'));
    }
}
