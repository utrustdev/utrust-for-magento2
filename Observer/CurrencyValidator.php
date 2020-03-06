<?php

declare(strict_types=1);

namespace Utrust\Payment\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Utrust\Payment\Model\Payment\Utrust;
use Utrust\Payment\Service\Config;

class CurrencyValidator implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * CurrencyValidator constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var MethodInterface $methodInstance */
        $methodInstance = $observer->getData('method_instance');

        if ('utrust' !== $methodInstance->getCode()) {
            return;
        }

        /** @var CartInterface|Quote $quote */
        $quote = $observer->getData('quote');

        /** @var DataObject $result */
        $result = $observer->getData('result');

        $availableCurrencies = $this->config->getAvailableCurrencies();

        if (empty($availableCurrencies)) {
            $result->setData('is_available', true);
        } else {
            $result->setData('is_available', in_array($quote->getBaseCurrencyCode(), $availableCurrencies));
        }
    }
}
