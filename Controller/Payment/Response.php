<?php
namespace Utrust\Payment\Controller\Payment;

class Response extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order && $order->getPayment()->getMethod() === 'utrust') {
            $this->_redirect('checkout/onepage/success');
        } else {
            $this->_redirect('/');
        }
    }
}
