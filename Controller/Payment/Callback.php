<?php
namespace Utrust\Payment\Controller\Payment;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Session;


class Callback extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var Utrust\Payment\Helper\Data
     */
    protected $helper;
    protected $checkoutSession;


    protected $quoteFactory;

    /**
     * @var Utrust\Payment\Logger\Logger
     */
    protected $logger;

    /**
     * @var Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Magento\Framework\DB\Transaction $transaction
     */
    protected $transaction;

    /**
     * Callback constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Utrust\Payment\Helper\Data $helper
     * @param \Utrust\Payment\Logger\Logger $logger
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Utrust\Payment\Helper\Data $helper,
        \Utrust\Payment\Logger\Logger $logger,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Checkout\Model\Session $checkoutSession,
        QuoteFactory $quoteFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute()
    {
        $response = "";
        $status = "200";

        try {
            $payload = json_decode($this->getRequest()->getContent(), true);
            // Calculate signature using the payload
            $signatureCalculated = $this->helper->getPayloadSignature($payload);

            // If signature from payload matches signature calculated
            if (isset($payload["signature"]) && $payload["signature"] === $signatureCalculated) {
                /** @var \Magento\Sales\Model\Order $order */
                $flow=$this->helper->getConfig('payment/utrust/checkout_flow/flow');
                
                $order = $this->orderFactory->create()->loadByIncrementId($payload["resource"]["reference"]);
                
                // ORDER PAID -> PROCESSING
                if (isset($payload["event_type"]) && $payload["event_type"] === "ORDER.PAYMENT.RECEIVED") {
                    if($flow){
                        $quote=$this->quoteFactory->create()->load($payload["resource"]["reference"]);
                            if($quote->getData('customer_id')==null){
                                $quote->setCustomerId(null)
                                ->setCustomerEmail($quote->getBillingAddress()->getEmail()) 
                                ->setCustomerIsGuest(true)
                                ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
                            }
                    
                        $result=$this->helper->createOrder($quote);
                        $this->checkoutSession->setLastQuoteId($quote->getId());
                        $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
                        $this->checkoutSession->setLastOrderId($result['orderid']);
                        $this->checkoutSession->setLastRealOrderId($result['success']);
                        $this->checkoutSession->setLastOrderStatus($result['status']);
                        $quoteOrder=$this->quoteFactory->create()->load($quote->getId());
                        $order = $this->orderFactory->create()->loadByIncrementId($quoteOrder->getReservedOrderId());
                    }
                    if($order){
                        $payment = $order->getPayment();
                        if ($payment->getMethod() === "utrust") {
                            if ($order->canInvoice()) {
                                $invoice = $this->invoiceService->prepareInvoice($order);
                                $invoice->register();
                                $invoice->save();
                                $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
                                $transactionSave->save();
                                $msg = __("Utrust Callback: ") . $payload["event_type"]
                                . "<br/>" . __("Amount: ") . $payload["resource"]["currency"] . " "
                                    . $payload["resource"]["amount"] . "<br/>";
                                $msg .= __("Invoice %1 created.", $invoice->getIncrementId());
                                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                                    ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                                $order->addStatusToHistory($order->getStatus(), $msg);
                                $order->save();
                            }
                        }
                    }
                }
                // ORDER CANCELLED -> CANCELLED
                elseif (isset($payload["event_type"]) && $payload["event_type"] === "ORDER.PAYMENT.CANCELLED") {
                    // If order is NOT CANCELED continues
                    if ($order->getState() !== \Magento\Sales\Model\Order::STATE_CANCELED) {
                        $order->cancel()->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                        $order->addStatusToHistory($order->getStatus(), "Utrust has canceled the payment (expired).");
                        $order->save();
                    }
                }
                // OTHER EVENT SHOULD BE DISCARDED
                else {
                    $response = "Event Error: event type is not ORDER.PAYMENT.RECEIVED or ORDER.PAYMENT.CANCELLED.\nEvent type: " . $payload["event_type"] . "\n";
                    $status = "500";
                }
            } else {
                $response = "Authentication error: signatures don't match.\nSignature from payload: " . $payload["signature"] . "\nSignature calculated: " . $signatureCalculated . "\n";
                $status = "500";
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());

            $response = $e->getMessage();
        }
        $this->getResponse()->setStatusCode($status)->setBody($response);
    }

    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
