<?php
namespace Utrust\Payment\Block;

class Display extends \Magento\Framework\View\Element\Template
{
    public $_template = 'Utrust_Payment::display.phtml';

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
        ) {
        parent::__construct($context);
        $this->cookieManager        = $cookieManager;
    }

    public function getUtrustPaymentIdValue()
    {
        return $this->cookieManager->getCookie('utrust_payment_id');
    }
}
