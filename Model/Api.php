<?php
namespace Utrust\Payment\Model;

class Api
{
    const API_SANDBOX_URL = "https://merchants.api.sandbox-utrust.com/api";
    const API_PRODUCTION_URL = "https://merchants.api.utrust.com/api";

    protected $helper;
    protected $apiUrl;
    protected $apiKey;

    public function __construct(
        \Utrust\Payment\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $sandbox = $helper->getConfig('payment/utrust/sandbox');
        $this->apiUrl = $sandbox ? self::API_SANDBOX_URL : self::API_PRODUCTION_URL;
        $this->apiKey = $helper->getConfig('payment/utrust/api_key');
    }

    public function pay($order)
    {
        $orderData = $this->helper->getOrderData($order);
        $data_string = json_encode($orderData);
        $authorization = "Authorization: Bearer " . $this->apiKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . "/stores/orders");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string), $authorization]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}
