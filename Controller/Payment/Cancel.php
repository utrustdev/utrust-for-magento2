<?php
namespace Utrust\Payment\Controller\Payment;

class Cancel extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;
    protected $cart;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order) {
            $order->cancel()->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->addStatusToHistory($order->getStatus(), 'Utrust has canceled the payment (buyer clicked canceled button).');
            $order->save();
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                try {
                    $this->cart->addOrderItem($item);
                } catch (\Exception $e) {
                    continue;
                }
            }
            $this->cart->save();
        }
        $this->_redirect('checkout/cart');
    }
}
