<?php
namespace Utrust\Payment\Block;

use Utrust\Payment\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class Info extends \Magento\Payment\Block\Info
{

    protected $coreRegistry;
    protected $helper;

    public $_template = 'Utrust_Payment::info.phtml';

    public function __construct(
        Template\Context $context,
        Registry $registry,
        \Utrust\Payment\Helper\Data $helper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getTransactionId()
    {
        $order = $this->coreRegistry->registry('current_order');
        if ($order) {
            return $order->getPayment()->getUtrustPaymentId();
        }
    }
}
