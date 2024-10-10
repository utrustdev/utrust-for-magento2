<?php
namespace Utrust\Payment\Controller\Payment;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Utrust\Payment\Helper\Data;

class Redirect extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;

    protected $api;

    protected $cookieManager;
    
    private $cookieMetadataFactory;

    /**
     * @var Utrust\Payment\Logger\Logger
     */
    protected $logger;

    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Utrust\Payment\Model\Api $api,
        \Utrust\Payment\Logger\Logger $logger,
        \Utrust\Payment\Helper\Data $helper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->checkoutSession       = $checkoutSession;
        $this->api                   = $api;
        $this->logger                = $logger;
        $this->helper                = $helper;
        $this->cookieManager         = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->cookieManager->getCookie('utrust_payment_id')) {
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath('/');

            $this->cookieManager->deleteCookie(
                'utrust_payment_id', $metadata);
        }
        if ($this->cookieManager->getCookie('quote_id')) {
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath('/');

            $this->cookieManager->deleteCookie(
                'quote_id', $metadata);
        }
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDurationOneYear();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);
        $flow = $this->helper->getConfig('payment/utrust/checkout_flow/flow');
        if ($flow) {
            $order = $this->checkoutSession->getQuote();
            if ($order) {
                $result = $this->api->pay($order);
                if (isset($result["data"]["type"]) && $result["data"]["type"] === "orders_redirect"
                    && isset($result["data"]["attributes"]["redirect_url"])) {
                    $payment = $order->getPayment();
                    $payment->setUtrustPaymentId($result["data"]["id"]);
                    $payment->save();
                    
                    $this->cookieManager->setPublicCookie(
                        'utrust_payment_id',
                        $result["data"]["id"],
                        $publicCookieMetadata
                    );
                    $this->cookieManager->setPublicCookie(
                        'quote_id',
                        $order->getId(),
                        $publicCookieMetadata
                    );
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setUrl($result["data"]["attributes"]["redirect_url"]);
                    return $resultRedirect;
                }
                return $this->_redirect('checkout/cart');
            }
        } else {
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
}
