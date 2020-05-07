<?php

namespace JouwWeb\DocData;

use JouwWeb\DocData\Type\AbstractRequest;
use JouwWeb\DocData\Type\ApproximateTotals;
use JouwWeb\DocData\Type\PaymentPreferences;
use JouwWeb\DocData\Type\StatusResponse;
use JouwWeb\DocData\Type\StatusSuccess;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DocData
{
    /**
     * API version of the DocData Order API this package is built for
     */
    const API_VERSION = '1.2';

    /**
     * @var Type\Merchant
     */
    private $merchant;

    /**
     * @var \SoapClient
     */
    private $soapClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $timeOut = 30;

    /**
     * @var string
     */
    private $userAgent;

    /**
     * @var array
     */
    private $classMaps = [
        'address'                 => '\JouwWeb\DocData\Type\Address',
        'amexPaymentInfo'         => '\JouwWeb\DocData\Type\AmexPaymentInfo',
        'amount'                  => '\JouwWeb\DocData\Type\Amount',
        'approximateTotals'       => '\JouwWeb\DocData\Type\ApproximateTotals',
        'authorization'           => '\JouwWeb\DocData\Type\Authorization',
        'bankTransferPaymentInfo' => '\JouwWeb\DocData\Type\BankTransferPaymentInfo',
        'cancelError'             => '\JouwWeb\DocData\Type\CancelError',
        'cancelSuccess'           => '\JouwWeb\DocData\Type\CancelSuccess',
        'capture'                 => '\JouwWeb\DocData\Type\Capture',
        'captureError'            => '\JouwWeb\DocData\Type\CaptureError',
        'captureSuccess'          => '\JouwWeb\DocData\Type\CaptureSuccess',
        'chargeback'              => '\JouwWeb\DocData\Type\Chargeback',
        'country'                 => '\JouwWeb\DocData\Type\Country',
        'createError'             => '\JouwWeb\DocData\Type\CreateError',
        'createSuccess'           => '\JouwWeb\DocData\Type\CreateSuccess',
        'destination'             => '\JouwWeb\DocData\Type\Destination',
        'error'                   => '\JouwWeb\DocData\Type\Error',
        'giftCardPaymentInfo'     => '\JouwWeb\DocData\Type\GiftCardPaymentInfo',
        'iDealPaymentInfo'        => '\JouwWeb\DocData\Type\IdealPaymentInfo',
        'invoice'                 => '\JouwWeb\DocData\Type\Invoice',
        'item'                    => '\JouwWeb\DocData\Type\Item',
        'language'                => '\JouwWeb\DocData\Type\Language',
        'maestroPaymentInfo'      => '\JouwWeb\DocData\Type\MaestroPaymentInfo',
        'masterCardPaymentInfo'   => '\JouwWeb\DocData\Type\MasterCardPaymentInfo',
        'menuPreferences'         => '\JouwWeb\DocData\Type\MenuPreferences',
        'merchant'                => '\JouwWeb\DocData\Type\Merchant',
        'misterCashPaymentInfo'   => '\JouwWeb\DocData\Type\MisterCashPaymentInfo',
        'name'                    => '\JouwWeb\DocData\Type\name',
        'payment'                 => '\JouwWeb\DocData\Type\Payment',
        'paymentInfo'             => '\JouwWeb\DocData\Type\PaymentInfo',
        'paymentPreferences'      => '\JouwWeb\DocData\Type\PaymentPreferences',
        'paymentReference'        => '\JouwWeb\DocData\Type\PaymentReference',
        'paymentRequestInput'     => '\JouwWeb\DocData\Type\PaymentRequestInput',
        'paymentResponse'         => '\JouwWeb\DocData\Type\PaymentResponse',
        'paymentSuccess'          => '\JouwWeb\DocData\Type\PaymentSuccess',
        'quantity'                => '\JouwWeb\DocData\Type\Quantity',
        'refund'                  => '\JouwWeb\DocData\Type\Refund',
        'refundError'             => '\JouwWeb\DocData\Type\RefundError',
        'refundSuccess'           => '\JouwWeb\DocData\Type\RefundSuccess',
        'riskCheck'               => '\JouwWeb\DocData\Type\RiskCheck',
        'shopper'                 => '\JouwWeb\DocData\Type\Shopper',
        'startError'              => '\JouwWeb\DocData\Type\StartError',
        'startSuccess'            => '\JouwWeb\DocData\Type\StartSuccess',
        'statusError'             => '\JouwWeb\DocData\Type\StatusError',
        'statusReport'            => '\JouwWeb\DocData\Type\StatusReport',
        'statusResponse'          => '\JouwWeb\DocData\Type\StatusResponse',
        'statusSuccess'           => '\JouwWeb\DocData\Type\StatusSuccess',
        'success'                 => '\JouwWeb\DocData\Type\Success',
        'vat'                     => '\JouwWeb\DocData\Type\Vat',
        'visaPaymentInfo'         => '\JouwWeb\DocData\Type\VisaPaymentInfo',
    ];

    /**
     * @var bool
     */
    private $test = false;

    /**
     * @param string          $merchantName
     * @param string          $merchantPassword
     * @param bool            $test
     * @param LoggerInterface $logger
     */
    public function __construct($merchantName, $merchantPassword, $test = false, LoggerInterface $logger = null)
    {
        $this->merchant = new Type\Merchant();
        $this->merchant->setName($merchantName);
        $this->merchant->setPassword($merchantPassword);

        $this->test = $test;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return int
     */
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * Set the timeout.
     *
     * After this time the request will stop. You should handle any errors triggered by this yourself.
     *
     * @param int $seconds The timeout in seconds.
     */
    public function setTimeOut($seconds)
    {
        $this->timeOut = (int)$seconds;
    }

    /**
     * Get the user-agent that will be used.
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set the user-agent for you application
     *
     * @param string $userAgent Your user-agent, it should look like <app-name>/<app-version>.
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = (string)$userAgent;
    }

    /**
     * @return string
     */
    private function getWsdl()
    {
        return sprintf(
            'https://%s.docdatapayments.com/ps/services/paymentservice/%s?wsdl',
            ($this->test === true ? 'test' : 'secure'),
            str_replace('.', '_', self::API_VERSION)
        );
    }

    /**
     * The goal of the create operation is solely to create a payment order on
     * Docdata Payments system. Creating a payment order is always the first
     * step of any workflow in Docdata Payments payment service.
     *
     * After an order is created, payments can be made on this order; either
     * through (the shopper via) the web menu or through the API by the
     * merchant. If the order has been created using information on specific
     * order items, the web menu can make use of this information by displaying
     * a shopping cart.
     *
     * @param string                       $paymentId
     * @param Type\Shopper                 $shopper
     * @param Type\Amount                  $totalGrossAmount
     * @param Type\Destination             $billTo
     * @param Type\PaymentPreferences|null $paymentPreferences
     * @param string|null                  $description
     * @param string|null                  $receiptText
     * @param Type\MenuPreferences|null    $menuPreferences
     * @param Type\PaymentRequest|null     $paymentRequest
     * @param Type\Invoice|null            $invoice
     * @param bool|null                    $includeCosts
     *
     * @return Type\CreateSuccess
     *
     * @throws \Exception
     */
    public function create(
        $paymentId,
        Type\Shopper $shopper,
        Type\Amount $totalGrossAmount,
        Type\Destination $billTo,
        Type\PaymentPreferences $paymentPreferences = null,
        $description = null,
        $receiptText = null,
        Type\MenuPreferences $menuPreferences = null,
        Type\PaymentRequest $paymentRequest = null,
        Type\Invoice $invoice = null,
        $includeCosts = null
    ) {
        $request = new Type\CreateRequest();
        $request->setMerchant($this->merchant);
        $request->setMerchantOrderReference($paymentId);
        $request->setShopper($shopper);
        $request->setTotalGrossAmount($totalGrossAmount);
        $request->setBillTo($billTo);
        if ($description !== null) {
            $request->setDescription($description);
        }
        if ($receiptText !== null) {
            $request->setReceiptText($receiptText);
        }
        if ($paymentPreferences === null) {
            $paymentPreferences = new PaymentPreferences();
        }
        $request->setPaymentPreferences($paymentPreferences);
        if ($menuPreferences !== null) {
            $request->setMenuPreferences($menuPreferences);
        }
        if ($paymentRequest !== null) {
            $request->setPaymentRequest($paymentRequest);
        }
        if ($invoice !== null) {
            $request->setInvoice($invoice);
        }
        if ($includeCosts !== null) {
            $request->setIncludeCosts($includeCosts);
        }

        // make the call
        $this->logger->info("Payment create: " . $paymentId, ['requestObject' => $this->createLoggableRequest($request)]);
        $response = $this->soap('create', [$request->toArray()]);
        $this->logger->info("Payment create soap response: " . $paymentId,
            ['response' => $this->soapClient->__getLastResponse()]);

        // validate response
        if (isset($response->createError)) {
            if ($this->test) {
                var_dump($this->soapClient->__getLastRequest());
            }

            $this->logger->error(
                "Payment create: " . $paymentId,
                ['error' => $response->createError->getError()->getExplanation()]
            );

            throw new \Exception($response->createError->getError()->getExplanation());
        }

        return $response->createSuccess;
    }

    /**
     * The goal of the start operation is to start payment on an existing order
     *
     * @param string                   $orderKey
     * @param Type\PaymentRequestInput $payment
     * @param Type\PaymentRequest      $recurringPaymentRequest
     *
     * @return Type\StartSuccess
     *
     * @throws \Exception
     */
    public function start(
        $orderKey,
        Type\PaymentRequestInput $payment = null,
        Type\PaymentRequest $recurringPaymentRequest = null
    ) {
        $request = new Type\StartRequest();
        $request->setPaymentOrderKey($orderKey);
        $request->setMerchant($this->merchant);
        if ($payment !== null) {
            $request->setPayment($payment);
        }
        if ($recurringPaymentRequest !== null) {
            $request->setRecurringPaymentRequest($recurringPaymentRequest);
        }

        // make the call
        $this->logger->info("Payment start: " . $orderKey, ['requestObject' => $this->createLoggableRequest($request)]);
        $response = $this->soap('start', [$request->toArray()]);
        $this->logger->info("Payment start soap response: " . $orderKey,
            ['response' => $this->soapClient->__getLastResponse()]);

        // validate response
        if (isset($response->startError)) {
            if ($this->test) {
                var_dump($this->soapClient->__getLastRequest());
            }

            $this->logger->error(
                "Payment start: " . $orderKey,
                ['error' => $response->startError->getError()->getExplanation()]
            );

            throw new \Exception($response->startError->getError()->getExplanation());
        }

        return $response->startSuccess;
    }

    /**
     * The cancel command is used for canceling a previously created payment,
     * and can only be used for payments with status NEW, STARTED and
     * AUTHORIZED.
     *
     * @param string $paymentOrderKey
     *
     * @return Type\CancelSuccess
     *
     * @throws \Exception
     */
    public function cancel($paymentOrderKey)
    {
        $request = new Type\CancelRequest();
        $request->setMerchant($this->merchant);
        $request->setPaymentOrderKey($paymentOrderKey);

        // make the call

        $this->logger->info("Payment cancel: " . $paymentOrderKey, ['requestObject' => $this->createLoggableRequest($request)]);
        $response = $this->soap('cancel', [$request->toArray()]);
        $this->logger->info("Payment cancel soap response: " . $paymentOrderKey,
            ['response' => $this->soapClient->__getLastResponse()]);

        // validate response
        if (isset($response->cancelError)) {
            if ($this->test) {
                var_dump($this->soapClient->__getLastRequest());
            }

            $this->logger->error(
                "Payment cancel: " . $paymentOrderKey,
                ['error' => $response->cancelError->getError()->getExplanation()]
            );

            throw new \Exception($response->cancelError->getError()->getExplanation());
        }

        return $response->cancelSuccess;
    }

    /**
     * The capture command is used to create requests for performing captures
     * on authorized payments. A merchant can choose to have it set up through
     * Docdata Payments back office to automatically have the full
     * authorization amount captured for each payment after a configured delay.
     *The capture command can then be used to overwrite this default capture.
     * If no default capture is configured, a merchant should use the capture
     * command to create one.
     *
     * @param string           $paymentId
     * @param string|null      $merchantCaptureReference
     * @param Type\Amount|null $amount
     * @param string|null      $itemCode
     * @param string|null      $description
     * @param bool|null        $finalCapture
     * @param bool|null        $cancelReserved
     * @param string|null      $requiredCaptureDate
     *
     * @return Type\CaptureSuccess
     *
     * @throws \Exception
     */
    public function capture(
        $paymentId,
        $merchantCaptureReference = null,
        Type\Amount $amount = null,
        $itemCode = null,
        $description = null,
        $finalCapture = null,
        $cancelReserved = null,
        $requiredCaptureDate = null
    ) {
        $request = new Type\CaptureRequest();
        $request->setMerchant($this->merchant);
        $request->setPaymentId($paymentId);

        if ($merchantCaptureReference !== null) {
            $request->setMerchantCaptureReference($merchantCaptureReference);
        }
        if ($amount !== null) {
            $request->setAmount($amount);
        }
        if ($itemCode !== null) {
            $request->setItemCode($itemCode);
        }
        if ($description !== null) {
            $request->setDescription($description);
        }
        if ($finalCapture !== null) {
            $request->setFinalCapture($finalCapture);
        }
        if ($cancelReserved !== null) {
            $request->setCancelReserved($cancelReserved);
        }
        if ($requiredCaptureDate !== null) {
            $request->setRequiredCaptureDate($requiredCaptureDate);
        }

        // make the call

        $this->logger->info("Payment capture: " . $merchantCaptureReference, ['requestObject' => $this->createLoggableRequest($request)]);
        $response = $this->soap('capture', [$request->toArray()]);
        $this->logger->info("Payment capture soap response: " . $merchantCaptureReference,
            ['response' => $this->soapClient->__getLastResponse()]);

        // validate response
        if (isset($response->captureError)) {
            if ($this->test) {
                var_dump($this->soapClient->__getLastRequest());
            }

            $this->logger->error(
                "Payment capture: " . $merchantCaptureReference,
                ['error' => $response->captureError->getError()->getExplanation()]
            );

            throw new \Exception($response->captureError->getError()->getExplanation());
        }

        return $response->captureSuccess;
    }

    /**
     * The refund command is used to create requests for performing one or more refunds on payments that have been
     * captured successfully. Its functionality is very similar to submitting captures.
     *
     * @param string               $paymentId
     * @param string               $merchantRefundReference
     * @param Type\Amount          $amount
     * @param string               $itemCode
     * @param string               $description
     * @param bool                 $cancelReserved
     * @param string               $requiredRefundDate
     * @param Type\SepaBankAccount $refundBankAccount
     *
     * @return Type\RefundSuccess
     *
     * @throws \Exception
     */
    public function refund(
        $paymentId,
        $merchantRefundReference = null,
        Type\Amount $amount = null,
        $itemCode = null,
        $description = null,
        $cancelReserved = null,
        $requiredRefundDate = null,
        Type\SepaBankAccount $refundBankAccount = null
    ) {
        $request = new Type\RefundRequest();
        $request->setMerchant($this->merchant);
        $request->setPaymentId($paymentId);

        if ($merchantRefundReference !== null) {
            $request->setMerchantRefundReference($merchantRefundReference);
        }
        if ($amount !== null) {
            $request->setAmount($amount);
        }
        if ($itemCode !== null) {
            $request->setItemCode($itemCode);
        }
        if ($description !== null) {
            $request->setDescription($description);
        }
        if ($cancelReserved !== null) {
            $request->setCancelReserved($cancelReserved);
        }
        if ($requiredRefundDate !== null) {
            $request->setRequiredRefundDate($requiredRefundDate);
        }
        if ($refundBankAccount !== null) {
            $request->setRefundBankAccount($refundBankAccount);
        }

        // make the call
        $this->logger->info("Payment capture: " . $merchantRefundReference, ['requestObject' => $this->createLoggableRequest($request)]);
        $response = $this->soap('refund', [$request->toArray()]);
        $this->logger->info("Payment capture soap response: " . $merchantRefundReference,
            ['response' => $this->soapClient->__getLastResponse()]);

        // validate response
        if (isset($response->refundError)) {
            if ($this->test) {
                var_dump($this->soapClient->__getLastRequest());
                var_dump($response->refundError);
            }

            $this->logger->error(
                "Payment capture: " . $merchantRefundReference,
                ['error' => $response->refundError->getError()->getExplanation()]
            );

            throw new \Exception(
                $response->refundError->getError()->getExplanation()
            );
        }

        return $response->refundSuccess;
    }

    /**
     * The status call can be used to get a report on the current status of a Payment Order, its payments and its
     * captures or refunds. It can be used to determine whether an order is considered paid, to retrieve a payment ID,
     * to get information on the statuses of captures/refunds.
     *
     * @param string $paymentOrderKey
     *
     * @return Type\StatusSuccess
     *
     * @throws \Exception
     */
    public function status($paymentOrderKey)
    {
        $request = new Type\StatusRequest();
        $request->setMerchant($this->merchant);
        $request->setPaymentOrderKey($paymentOrderKey);

        // make the call
        $this->logger->info("Payment status: " . $paymentOrderKey, ['requestObject' => $this->createLoggableRequest($request)]);

        /** @var StatusResponse $response */
        $response = $this->soap('status', [$request->toArray()]);
        $this->logger->info("Payment status soap response: " . $paymentOrderKey,
            ['response' => $this->soapClient->__getLastResponse()]);

        // validate response
        if ($response->getStatusError()) {
            if ($this->test) {
                var_dump($this->soapClient->__getLastRequest());
                var_dump($response->getStatusError());
            }

            $this->logger->error(
                "Payment status: " . $paymentOrderKey,
                ['error' => $response->getStatusError()->getError()->getExplanation()]
            );

            throw new \Exception($response->getStatusError()->getError()->getExplanation());
        }

        return $response->getStatusSuccess();
    }

    /**
     * Get the payment url
     *
     * @param string $clientLanguage
     * @param string $paymentClusterKey
     * @param string $successUrl           Merchant’s web page where the shopper will be sent to after a
     *                                     successful transaction. Mandatory in back office.
     * @param string $canceledUrl          Merchant’s web page where the shopper will be sent to if they
     *                                     cancel their transaction. Mandatory in back office.
     * @param string $pendingUrl           Merchant’s web page where the shopper will be sent to if a
     *                                     payment is started successfully but not yet paid.
     * @param string $errorUrl             Merchant’s web page where the shopper will be sent to if an
     *                                     error occurs.
     * @param string $defaultPaymentMethod ID of the default payment method.
     * @param string $defaultAct           If a default payment method is declared to direct the shopper
     *                                     to that payment method in the payment menu. Can contain the
     *                                     values “yes” or “no”.
     * @param string $idealIssuerId        If a default payment method needs an issuer id, allow it to
     *                                     be passed. For example, the payment method 'IDEAL' needs an
     *                                     issuer id corresponding to a connected bank, e.g. 'RABONL2U'
     *
     * @return string
     */
    public function getPaymentUrl(
        $clientLanguage,
        $paymentClusterKey,
        $successUrl = null,
        $canceledUrl = null,
        $pendingUrl = null,
        $errorUrl = null,
        $defaultPaymentMethod = null,
        $defaultAct = null,
        $idealIssuerId = null
    ) {
        $parameters = [];
        $parameters['command'] = 'show_payment_cluster';
        $parameters['merchant_name'] = $this->merchant->getName();
        $parameters['client_language'] = (string)$clientLanguage;
        $parameters['payment_cluster_key'] = (string)$paymentClusterKey;

        if ($successUrl !== null) {
            $parameters['return_url_success'] = $successUrl;
        }
        if ($canceledUrl !== null) {
            $parameters['return_url_canceled'] = $canceledUrl;
        }
        if ($pendingUrl !== null) {
            $parameters['return_url_pending'] = $pendingUrl;
        }
        if ($errorUrl !== null) {
            $parameters['return_url_error'] = $errorUrl;
        }
        if ($defaultPaymentMethod !== null) {
            $parameters['default_pm'] = strtoupper($defaultPaymentMethod);

            if (in_array($parameters['default_pm'], ['IDEAL', 'PAYPAL'])
                && in_array($defaultAct, [true, 1, 'yes', 'YES'])) {
                $parameters['default_act'] = 'yes';
            }

            if ($parameters['default_pm'] === 'IDEAL' && $idealIssuerId !== null) {
                $parameters['ideal_issuer_id'] = $idealIssuerId;
            }
        }

        if ($this->test) {
            $base = 'https://test.docdatapayments.com/ps/menu';
        } else {
            $base = 'https://secure.docdatapayments.com/ps/menu';
        }

        // build the url
        return $base . '?' . http_build_query($parameters);
    }

    /**
     * Redirect to the payment url
     *
     * @param string $clientLanguage
     * @param string $paymentClusterKey
     * @param string $successUrl           Merchant’s web page where the shopper will be sent to after a
     *                                     successful transaction. Mandatory in back office.
     * @param string $canceledUrl          Merchant’s web page where the shopper will be sent to if they
     *                                     cancel their transaction. Mandatory in back office.
     * @param string $pendingUrl           Merchant’s web page where the shopper will be sent to if a
     *                                     payment is started successfully but not yet paid.
     * @param string $errorUrl             Merchant’s web page where the shopper will be sent to if an
     *                                     error occurs.
     * @param string $defaultPaymentMethod ID of the default payment method.
     * @param string $defaultAct           If a default payment method is declared to direct the shopper
     *                                     to that payment method in the payment menu. Can contain the
     *                                     values “yes” or “no”.
     * @param string $issuerId             If a default payment method needs an issuer id, allow it to
     *                                     be passed. For example, the payment method 'IDEAL' needs an
     *                                     issuer id corresponding to a connected bank, e.g. 'RABONL2U'
     */
    public function redirectToPaymentUrl(
        $clientLanguage,
        $paymentClusterKey,
        $successUrl = null,
        $canceledUrl = null,
        $pendingUrl = null,
        $errorUrl = null,
        $defaultPaymentMethod = null,
        $defaultAct = null,
        $issuerId = null
    ) {
        // get the url
        $url = $this->getPaymentUrl(
            $clientLanguage,
            $paymentClusterKey,
            $successUrl,
            $canceledUrl,
            $pendingUrl,
            $errorUrl,
            $defaultPaymentMethod,
            $defaultAct,
            $issuerId
        );

        $this->logger->info("Redirect to docdata: " . $url);

        // redirect
        header('location: ' . $url);
        exit();
    }

    /**
     * Docdata document: 733126_Integration_manual_Order_Api_1-1.pdf
     * Chapter: 7.4 Determining whether an order is paid
     * Determining whether an order is paid
     * Different merchants can have different ways of determining when they consider an order “paid”,
     * the totals in the status report are there to help make this decision. Keep in mind that the status report
     * never reports about money actually having been transferred to a merchant, so it is not a complete guarantee
     * that a payment has been finished in that sense.
     * Using the totals to determine a level of confidence:
     *
     * @param $paymentOrderKey
     *
     * @return int The highest Paid level (from NotPaid to SafeRoute)
     * @throws \Exception
     */
    public function statusPaid($paymentOrderKey)
    {
        return $this->getPaidLevel($this->status($paymentOrderKey));
    }

    /**
     * @param StatusSuccess|null $statusSuccess
     *
     * @return int
     */
    public function getPaidLevel(StatusSuccess $statusSuccess = null)
    {
        if ($statusSuccess &&
            $statusSuccess->getSuccess() != null &&
            $statusSuccess->getSuccess()->getCode() == 'SUCCESS' &&
            $statusSuccess->getReport() != null &&
            $statusSuccess->getReport()->getApproximateTotals() != null
        ) {
            /** @var ApproximateTotals $approximateTotals */
            $approximateTotals = $statusSuccess->getReport()->getApproximateTotals();

            //Safe Route
            if (($approximateTotals->getTotalRegistered() == $approximateTotals->getTotalCaptured())) {
                return Type\PaidLevel::SafeRoute;
            }

            //Balanced Route
            if ($approximateTotals->getTotalRegistered() ==
                $approximateTotals->getTotalAcquirerApproved()
            ) {
                return Type\PaidLevel::BalancedRoute;
            }

            //Quick Route
            if ($approximateTotals->getTotalRegistered() ==
                ($approximateTotals->getTotalShopperPending()
                    + $approximateTotals->getTotalAcquirerPending()
                    + $approximateTotals->getTotalAcquirerApproved())
            ) {
                return Type\PaidLevel::QuickRoute;
            }
        }

        return Type\PaidLevel::NotPaid;
    }

    /**
     * List of available banks (issuers) for the iDeal payment method
     *
     * @return array
     */
    public function getIdealIssuers()
    {
        return require __DIR__ . '/../config/ideal-issuers.php';
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    protected function soap($method, array $args = [])
    {
        return call_user_func_array([$this->getSoapClient(), $method], $args);
    }

    /**
     * @return \SoapClient
     */
    protected function getSoapClient()
    {
        // create the client if needed
        if (!$this->soapClient) {
            $options = [
                'trace'              => true,
                'exceptions'         => true,
                'connection_timeout' => $this->getTimeOut(),
                'user_agent'         => $this->getUserAgent(),
                'cache_wsdl'         => $this->test ? WSDL_CACHE_NONE : WSDL_CACHE_BOTH,
                'classmap'           => $this->classMaps,
                // Don't reuse the connection to prevent the default_socket_timeout INI option from forcibly closing it
                // before the timeout is reached.
                'keep_alive'         => false,
            ];

            $this->soapClient = new \SoapClient($this->getWsdl(), $options);
        }

        return $this->soapClient;
    }

    /**
     * @param $request AbstractRequest
     * @return array
     */
    protected function createLoggableRequest($request)
    {
        $representation = $request->toArray();

        if (isset($representation['merchant']['password'])) {
            $representation['merchant']['password'] = 'XXXXXX';
        }

        return $representation;
    }
}
