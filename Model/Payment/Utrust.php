<?php

namespace Utrust\Payment\Model\Payment;

class Utrust extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = "utrust";
    protected $_isOffline = true;

    protected $_infoBlockType = \Utrust\Payment\Block\Info::class;
}
