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
    protected $_cookieManager;
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
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $collection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Api\GuestCartManagementInterface $guestCart
    ) {
        $this->checkoutSession       = $checkoutSession;
        $this->helper                = $helper;
        $this->cookieManager         = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->collection            = $collection;
        $this->orderRepository       = $orderRepository;
        $this->guestCart             = $guestCart;
        parent::__construct($context);
    }

    public function execute()
    {
        $flow = $this->helper->getConfig('payment/utrust/checkout_flow/flow');
        if ($flow) {
            $this->collection->addFieldToFilter('utrust_payment_id', $this->cookieManager->getCookie('utrust_payment_id'));

            foreach ($this->collection as $value) {
                $orderId = $value->getId();
            }
            if (empty($orderId)) {
                sleep(60);
                $this->collection->addFieldToFilter('utrust_payment_id', $this->cookieManager->getCookie('utrust_payment_id'));

                foreach ($this->collection as $value) {
                    $orderId = $value->getId();
                }
                if (empty($orderId)) {
                    sleep(60);
                    $this->collection->addFieldToFilter('utrust_payment_id', $this->cookieManager->getCookie('utrust_payment_id'));

                    foreach ($this->collection as $value) {
                        $orderId = $value->getId();
                    }
                }
            }
            $order = $this->orderRepository->get($orderId);
            $this->checkoutSession->setLastSuccessQuoteId($order->getQouteId());
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
