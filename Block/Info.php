<?php
namespace Utrust\Payment\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class Info extends \Magento\Payment\Block\Info
{

    protected $coreRegistry;

    public $_template = 'Utrust_Payment::info.phtml';

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ){
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getTransactionId()
    {
        $order = $this->coreRegistry->registry('current_order');
        if($order){
            return $order->getPayment()->getUtrustPaymentId();
        }
    }

}