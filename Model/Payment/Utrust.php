<?php


namespace Utrust\Payment\Model\Payment;

use Magento\Directory\Helper\Data as DirectoryHelper;

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
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
}
