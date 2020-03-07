<?php

declare(strict_types=1);

namespace Utrust\Payment\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_CURRENCY = 'payment/utrust/currency';
    const XML_PATH_INSTRUCTIONS = 'payment/utrust/instructions';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param null $store
     * @return array
     */
    public function getAvailableCurrencies($store = null): array
    {
        $result = $this->scopeConfig->getValue(
            self::XML_PATH_CURRENCY,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );

        if (!empty($result)) {
            return explode(',', $result);
        } else {
            return [];
        }
    }

    /**
     * @param null $store
     * @return string
     */
    public function getInstructions($store = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_INSTRUCTIONS,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }
}
