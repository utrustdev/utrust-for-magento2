<?php
namespace Utrust\Payment\Controller\Payment;


use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Callback extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var Utrust\Payment\Helper\Data
     */
    protected $helper;

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
        \Magento\Framework\DB\Transaction $transaction
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
    }


    public function execute()
    {
        $resultado = "0";
        try{
            $payload = json_decode($this->getRequest()->getContent(), true);
            $signature = $this->helper->getPayloadSignature($payload);
            if(isset($payload["signature"]) && $payload["signature"] === $signature){
                if(isset($payload["event_type"]) && $payload["event_type"] === 'ORDER.PAYMENT.RECEIVED') {
                    /** @var \Magento\Sales\Model\Order $order */
                    $order = $this->orderFactory->create()->loadByIncrementId($payload["resource"]["reference"]);
                    $payment = $order->getPayment();
                    if($payment->getMethod() === 'utrust') {
                        if($order->canInvoice()){
                            $invoice = $this->invoiceService->prepareInvoice($order);
                            $invoice->register();
                            $invoice->save();
                            $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
                            $transactionSave->save();
                            $msg = __('Utrust Callback: ').$payload["event_type"]."<br/>".__("Amount: ").$payload["resource"]["currency"]." ".$payload["resource"]["amount"]."<br/>";
                            $msg .= __("Invoice %1 created.", $invoice->getIncrementId());
                            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                            $order->addStatusToHistory($order->getStatus(), $msg);
                            $order->save();
                        }
                    }
                }
                $resultado = "1";
            }
        }
        catch(\Exception $e){
            $this->logger->info($e->getMessage());
        }
        $this->getResponse()->setBody($resultado);
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