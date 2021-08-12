<?php
namespace Utrust\Payment\Block;

class Display extends \Magento\Framework\View\Element\Template
{
    public $_template = 'Utrust_Payment::display.phtml';
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context) {
        parent::__construct($context);
    }

    public function getUtrustPaymentIdValue()
    {
        return $this->cookieManager->getCookie('utrust_payment_id');
    }
}
