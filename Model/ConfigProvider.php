<?php
namespace Utrust\Payment\Model;

use \Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\Escaper;
use \Magento\Framework\UrlInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'utrust';

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod
     */
    protected $method;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        UrlInterface $urlInterface
    ){
        $this->method = $paymentHelper->getMethodInstance(self::CODE);
        $this->escaper = $escaper;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'instructions' => [
                    self::CODE => $this->method->getInstructions()
                ],
                self::CODE => [
                    'redirectUrl' => $this->urlInterface->getUrl('utrust/payment/redirect')
                ]
            ]
        ];
        return $config;
    }
}