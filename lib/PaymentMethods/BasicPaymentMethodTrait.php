<?php

namespace Heidelpay\PhpPaymentApi\PaymentMethods;

use Heidelpay\PhpPaymentApi\Adapter\HttpAdapterInterface;
use Heidelpay\PhpPaymentApi\Constants\ApiConfig;
use Heidelpay\PhpPaymentApi\Constants\TransactionMode;
use Heidelpay\PhpPaymentApi\Exceptions\UndefinedTransactionModeException;
use Heidelpay\PhpPaymentApi\Request as HeidelpayRequest;

/**
 * This classe is the basic payment method trait
 *
 * It contains the main properties and functions for
 * every payment method
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present Heidelberger Payment GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/heidelpay-php-api/
 *
 * @author  Jens Richter
 *
 * @package heidelpay\php-payment-api\paymentmethods
 */
trait BasicPaymentMethodTrait
{
    /**
     * HTTP Adapter for payment connection
     *
     * @var HttpAdapterInterface
     */
    protected $adapter;

    /**
     * Heidelpay request object
     *
     * @var \Heidelpay\PhpPaymentApi\Request
     */
    protected $request;

    /**
     * Heidelpay request array
     *
     * @var array request
     */
    protected $requestArray;

    /**
     * Heidelpay response object
     *
     * @var \Heidelpay\PhpPaymentApi\Response
     */
    protected $response;

    /**
     * Heidelpay response array
     *
     * @var array response
     */
    protected $responseArray;

    /**
     * Dry run
     *
     * If set to true request will be generated but not send to payment api.
     * This is use full for testing.
     *
     * @var boolean dry run
     */
    public $dryRun = false;

    /**
     * Returns the payment code for the payment request.
     *
     * @return string
     */
    public function getPaymentCode()
    {
        if (!property_exists($this, 'paymentCode')) {
            return null;
        }

        return $this->paymentCode;
    }

    /**
     * Returns the brand for the payment method.
     *
     * @return string
     */
    public function getBrand()
    {
        if (!property_exists($this, 'brand')) {
            return null;
        }

        return $this->brand;
    }

    /**
     * Return the name of the used class
     *
     * @return string class name
     */
    public static function getClassName()
    {
        return substr(strrchr(get_called_class(), '\\'), 1);
    }

    /**
     * @inheritdoc
     */
    public function setRequest(HeidelpayRequest $heidelpayRequest)
    {
        $this->request = $heidelpayRequest;
    }

    /**
     * @inheritdoc
     */
    public function getRequest()
    {
        if ($this->request === null) {
            return $this->request = new HeidelpayRequest();
        }

        return $this->request;
    }

    /**
     * @inheritdoc
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @inheritdoc
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @inheritdoc
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @inheritdoc
     *
     * @throws UndefinedTransactionModeException
     */
    public function getPaymentUrl()
    {
        $mode = $this->getRequest()->getTransaction()->getMode();

        if ($mode === null) {
            throw new UndefinedTransactionModeException('Transaction mode is not set');
        }

        if ($mode === TransactionMode::LIVE) {
            return ApiConfig::LIVE_URL;
        }

        return ApiConfig::TEST_URL;
    }

    /**
     * This function prepares the request for heidelpay api
     *
     * It will add the used payment method  and the brand to the request. If
     * dry run is set the request will only be convert to an array.
     *
     * @throws UndefinedTransactionModeException
     */
    public function prepareRequest()
    {
        $this->getRequest()->getCriterion()->set('payment_method', $this->getClassName());
        if ($this->getBrand() !== null) {
            $this->getRequest()->getAccount()->setBrand($this->brand);
        }

        $uri = $this->getPaymentUrl();
        $this->requestArray = $this->getRequest()->convertToArray();

        if ($this->dryRun === false && $uri !== null && is_array($this->requestArray)) {
            list($this->responseArray, $this->response) =
                $this->getRequest()->send($uri, $this->requestArray, $this->getAdapter());
        }
    }

    /**
     * Returns an array for a json representation.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $return = [];
        foreach (get_object_vars($this) as $field => $value) {
            $return[$field] = $value;
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
