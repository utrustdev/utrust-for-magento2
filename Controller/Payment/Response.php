<?php
namespace Utrust\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\QuoteFactory;
use Utrust\Payment\Helper\Data;

class Response extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;
    protected $helper;
    protected $guestCart;
    protected $cookieManager;
    private $cookieMetadataFactory;
    protected $collection;
    protected $orderRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Utrust\Payment\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $collection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        QuoteFactory $quoteFactory,
        \Magento\Quote\Api\GuestCartManagementInterface $guestCart
    ) {
        $this->checkoutSession       = $checkoutSession;
        $this->helper                = $helper;
        $this->cookieManager         = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->collection            = $collection;
        $this->orderRepository       = $orderRepository;
        $this->guestCart             = $guestCart;
        $this->orderFactory          = $orderFactory;
        $this->quoteFactory          = $quoteFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        sleep(50);
        $flow = $this->helper->getConfig('payment/utrust/checkout_flow/flow');
        if ($flow) {
            $quoteId    = $this->cookieManager->getCookie('quote_id');
            $quote      = $this->quoteFactory->create()->load($quoteId);
            $quoteOrder = $this->quoteFactory->create()->load($quote->getId());
            $order      = $this->orderFactory->create()->loadByIncrementId($quoteOrder->getReservedOrderId());
            $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
            $this->checkoutSession->setLastQuoteId($order->getQuoteId());
            $this->checkoutSession->setLastOrderId($order->getEntityId());
            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->checkoutSession->setLastOrderStatus('pending');
            $this->guestCart->createEmptyCart();
            $this->_redirect('checkout/onepage/success');
        } else {
            $order = $this->checkoutSession->getLastRealOrder();
            if ($order && $order->getPayment()->getMethod() === 'utrust') {
                $this->_redirect('checkout/onepage/success');
            } else {
                $this->_redirect('/');
            }
        }
    }
}
