<?php
namespace Utrust\Paayment\Model\SessionCustom;

class Storage extends \Magento\Framework\Session\Storage
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $namespace = 'mysession',
        array $data = []
    ) {
        parent::__construct($namespace, $data);
    }
}