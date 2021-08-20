<?php
namespace Utrust\Payment\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{   
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ) {
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        parent::__construct($context);
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getOrderData($order)
    {   

        $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = [
                "sku" => $item["sku"],
                "name" => $item["name"],
                "price" => number_format($item["base_price_incl_tax"], 2, '.', ''),
                "currency" => $order->getBaseCurrencyCode(),
                "quantity" => (int) $item["qty_ordered"],
            ];
        }

        $shippingAmount = (float) $order->getBaseShippingInclTax();

        $amountData = [
            'total' => $order->getBaseGrandTotal(),
            'currency' => $order->getBaseCurrencyCode(),
            'details' => [
                'subtotal' => $order->getBaseSubtotal(),
                'tax' => $order->getBaseTaxAmount(),
                'shipping' => number_format($shippingAmount, 2, '.', ''),
                'discount' => $order->getDiscountAmount(),
            ],
        ];

        $returnUrlsData = [
            'return_url' => $this->_urlBuilder->getUrl('utrust/payment/response', ['_secure' => true]),
            'cancel_url' => $this->_urlBuilder->getUrl('utrust/payment/cancel', ['_secure' => true]),
            'callback_url' => $this->_urlBuilder->getUrl('utrust/payment/callback', ['_secure' => true]),
        ];

        $customerData = [

            'first_name' => $order->getCustomerFirstname(),
            'last_name' => $order->getCustomerLastname(),
            'email' => $order->getCustomerEmail(),
            'address1' => $order->getBillingAddress()->getStreet1(),
            'address2' => $order->getBillingAddress()->getStreet2(),
            'city' => $order->getBillingAddress()->getCity(),
            'state' => $order->getBillingAddress()->getRegion(),
            'postcode' => $order->getBillingAddress()->getPostcode(),
            'country' => $order->getBillingAddress()->getCountryId(),
        ];

        $data = [
            'data' => [
                'type' => 'orders',
                'attributes' => [
                    'order' => [
                        'reference' => $order->getIncrementId(),
                        'amount' => $amountData,
                        'return_urls' => $returnUrlsData,
                        'line_items' => $items,
                    ],
                    'customer' => $customerData,
                ],
            ],
        ];
        return $data;
    }

    public function getQuoteData($order){
       $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = [
                "sku" => $item["sku"],
                "name" => $item["name"],
                "price" => number_format($item["base_price_incl_tax"], 2, '.', ''),
                "currency" => $order->getBaseCurrencyCode(),
                "quantity" => (int) $item["qty"],
            ];
        }
        $shippingAmount = (float) $order->getBaseGrandTotal()-$order->getBaseSubtotalWithDiscount();
        $discountAmount = (float) $order->getBaseSubtotal()-$order->getBaseSubtotalWithDiscount() ;

        $amountData = [
            'total' => $order->getBaseGrandTotal(),
            'currency' => $order->getBaseCurrencyCode(),
            'details' => [
                'subtotal' => $order->getBaseSubtotal(),
                'tax' => $order->getBaseTaxAmount(),
                'shipping' => number_format($shippingAmount, 2, '.', ''),
                'discount' => number_format($discountAmount, 2, '.', ''),
            ],
        ];

        $returnUrlsData = [
            'return_url' => $this->_urlBuilder->getUrl('utrust/payment/response', ['_secure' => true]),
            'cancel_url' => $this->_urlBuilder->getUrl('utrust/payment/cancel', ['_secure' => true]),
            'callback_url' => $this->_urlBuilder->getUrl('utrust/payment/callback', ['_secure' => true]),
        ];

        $customerData = [
            'first_name' => $order->getBillingAddress()->getFirstName(),
            'last_name' => $order->getBillingAddress()->getLastName(),
            'email' => $order->getBillingAddress()->getEmail(),
            'address1' => $order->getBillingAddress()->getStreet1(),
            'address2' => $order->getBillingAddress()->getStreet2(),
            'city' => $order->getBillingAddress()->getCity(),
            'state' => $order->getBillingAddress()->getRegion(),
            'postcode' => $order->getBillingAddress()->getPostcode(),
            'country' => $order->getBillingAddress()->getCountryId(),
        ];

        $data = [
            'data' => [
                'type' => 'orders',
                'attributes' => [
                    'order' => [
                        'reference' => $order->getId(),
                        'amount' => $amountData,
                        'return_urls' => $returnUrlsData,
                        'line_items' => $items,
                    ],
                    'customer' => $customerData,
                ],
            ],
        ];
        return $data;
    }


    public function createOrder($orderInfo) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
                            $logger = new \Zend\Log\Logger();
                            $logger->addWriter($writer);
                            $logger->info(json_encode($orderInfo->getData()));
        // Create Order From Quote Object
            $order = $this->quoteManagement->submit($orderInfo);
            $logger->info(json_encode($order->getData()));
            $order->getPayment()->setUtrustPaymentId($orderInfo->getPayment()->getUtrustPaymentId());
            $order->getPayment()->save();
        /* for send order email to customer email id */
        $this->orderSender->send($order);
        /* get order real id from order */
        $orderId = $order->getIncrementId();
        if($orderId){
            $result['success']= $orderId;
            $result['orderid']=$order->getId();
            $result['status']=$order->getStatus();
        }else{
            $result=['error'=>true,'msg'=>'Error occurs for Order placed'];
        }
        return $result;
    }
    
    /**
     *
     * @param array $payload
     * @return string
     */
    public function getPayloadSignature($payload)
    {
        unset($payload["signature"]);
        $payload = $this->arrayFlatten($payload);
        ksort($payload);
        $msg = implode("", array_map(function ($v, $k) {
            return $k . $v;
        }, $payload, array_keys($payload)));
        $secret = $this->getConfig('payment/utrust/credentials/webhook_secret');
        $signed_message = hash_hmac("sha256", $msg, $secret);
        return $signed_message;
    }

    protected function arrayFlatten(array $array, $parentKey = '')
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $result[$key . $k] = $v;
                }
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
