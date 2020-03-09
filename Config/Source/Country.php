<?php

declare(strict_types=1);

namespace Utrust\Payment\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Utrust\Payment\Service\Config;

class Country implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var Collection
     */
    private $countryCollection;

    /**
     * @var Config
     */
    private $config;

    /**
     * Country constructor.
     * @param Collection $countryCollection
     * @param Config $config
     */
    public function __construct(
        Collection $countryCollection,
        Config $config
    ) {
        $this->countryCollection = $countryCollection;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (!$this->options) {
            $options = $this->countryCollection->loadData()->toOptionArray(
                false
            );

            $this->options = array_filter(
                $options,
                function ($option) {
                    return !in_array($option['value'], $this->config->getRestrictedCountryCodes()) ?
                        $option :
                        null;
                }
            );
        }

        return $this->options;
    }
}
