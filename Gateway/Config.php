<?php

declare(strict_types=1);

namespace Utrust\Payment\Gateway;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $config;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param array $config
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $config = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getApiUrl($storeId = null): string
    {
        $sandbox = $this->scopeConfig->getValue(
            'payment/utrust/credentials/sandbox',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return (string) ($sandbox ? $this->config['api_url_sandbox'] : $this->config['api_url']);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getApiKey($storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            'payment/utrust/credentials/api_key',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
