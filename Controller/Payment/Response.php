<?php
namespace Utrust\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\QuoteFactory;
use Utrust\Payment\Helper\Data;

class Response extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;
    protected $helper;
    protected $invoiceService;
    protected $transaction;
    protected $orderFactory;
    protected $_resultPageFactory;
    protected $quoteFactory;
    protected $_eventManager;
    protected $guestCart;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Utrust\Payment\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\Url $urlHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        QuoteFactory $quoteFactory,
        \Magento\Quote\Api\GuestCartManagementInterface $guestCart
    ) {
        $this->checkoutSession    = $checkoutSession;
        $this->helper             = $helper;
        $this->invoiceService     = $invoiceService;
        $this->transaction        = $transaction;
        $this->orderFactory       = $orderFactory;
        $this->_urlHelper         = $urlHelper;
        $this->_eventManager      = $eventManager;
        $this->_resultPageFactory = $resultPageFactory;
        $this->quoteFactory       = $quoteFactory;
        $this->guestCart          = $guestCart;
        parent::__construct($context);
    }

    public function execute()
    {
        $flow = $this->helper->getConfig('payment/utrust/checkout_flow/flow');
        if ($flow) {
            $this->guestCart->createEmptyCart();
            return $this->_resultPageFactory->create();
        } else {
            $order = $this->checkoutSession->getLastRealOrder();
            if ($order && $order->getPayment()->getMethod() === 'utrust') {
                $this->_redirect('checkout/onepage/success');
            } else {
                $this->_redirect('/');
            }
        }
    }
}
