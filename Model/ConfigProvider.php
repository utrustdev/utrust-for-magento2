<?php

declare(strict_types=1);

namespace Utrust\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Model\CcConfig;
use Utrust\Payment\Service\Config;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'utrust';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * ConfigProvider constructor.
     * @param UrlInterface $url
     * @param CcConfig $ccConfig
     * @param Config $config
     */
    public function __construct(
        UrlInterface $url,
        CcConfig $ccConfig,
        Config $config
    ) {
        $this->url = $url;
        $this->ccConfig = $ccConfig;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'redirectUrl' => $this->url->getUrl('utrust/payment/redirect'),
                    'logoUrl' => $this->ccConfig->getViewFileUrl('Utrust_Payment::images/utrust-logo.png'),
                    'instructions' => $this->config->getInstructions(),
                ],
            ],
        ];
    }
}
