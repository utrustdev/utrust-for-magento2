<?php
namespace Utrust\Payment\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

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
