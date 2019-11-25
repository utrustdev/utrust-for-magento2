<?php
namespace Utrust\Payment\Controller\Payment;

class Redirect extends \Magento\Framework\App\Action\Action
{

    protected $checkoutSession;

    protected $api;

    /**
     * @var Utrust\Payment\Logger\Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Utrust\Payment\Model\Api $api,
        \Utrust\Payment\Logger\Logger $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->api = $api;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order) {
            $result = $this->api->pay($order);
            if (isset($result["data"]["type"]) && $result["data"]["type"] === "orders_redirect"
                && isset($result["data"]["attributes"]["redirect_url"])) {
                $payment = $order->getPayment();
                $payment->setUtrustPaymentId($result["data"]["id"]);
                $payment->save();
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($result["data"]["attributes"]["redirect_url"]);
                return $resultRedirect;
            }
            $this->logger->info(json_encode($result));
            return $this->_redirect('checkout/cart');
        }
    }
}
