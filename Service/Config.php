<?php

declare(strict_types=1);

namespace Utrust\Payment\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_CURRENCY = 'payment/utrust/currency';
    const XML_PATH_INSTRUCTIONS = 'payment/utrust/instructions';
    const XML_PATH_RESTRICTED_COUNTRY_CODES = 'payment/utrust/restricted_country_codes';

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
        $result = (string) $this->scopeConfig->getValue(
            self::XML_PATH_CURRENCY,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );

        return !empty($result) ? explode(',', $result) : [];
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

    /**
     * @param null $store
     * @return array
     */
    public function getRestrictedCountryCodes($store = null): array
    {
        $codes = (string) $this->scopeConfig->getValue(
            self::XML_PATH_RESTRICTED_COUNTRY_CODES,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );

        return !empty($codes) ? explode(',', $codes) : [];
    }
}
