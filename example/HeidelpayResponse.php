<?php
/**
 * heidelpay response action
 *
 * This is a coding example for the response action
 *
 *
 * @license Use of this software requires acceptance of the License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/heidelpay-php-payment-api/
 *
 * @author  Jens Richter
 *
 * @category example
 */

/*
 * For security reason all examples are disabled by default.
 */
require_once './_enableExamples.php';
if (defined('HEIDELPAY_PHP_PAYMENT_API_EXAMPLES') && HEIDELPAY_PHP_PAYMENT_API_EXAMPLES !== true) {
    exit();
}

/*Require the composer autoloader file */
require_once __DIR__ . '/../../../autoload.php';

$heidelpayResponse = new  Heidelpay\PhpPaymentApi\Response($_POST);

$secretPass = '39542395235ßfsokkspreipsr';

$identificationTransactionId = $heidelpayResponse->getIdentification()->getTransactionId();

try {
    $heidelpayResponse->verifySecurityHash($secretPass, $identificationTransactionId);
} catch (\Exception $e) {
    /* If the verification does not match this can mean some kind of manipulation or
     * miss configuration. So you can log $e->getMessage() for debugging.*/
    return;
}

if ($heidelpayResponse->isSuccess()) {
    
    /* save order and transaction result to your database */
    if ($heidelpayResponse->isPending()) {
        /* use this to set the order status to pending */
    }
    /* redirect customer to success page */
    echo HEIDELPAY_PHP_PAYMENT_API_URL . HEIDELPAY_PHP_PAYMENT_API_FOLDER . 'HeidelpaySuccess.php';
    
    /*save order */
} elseif ($heidelpayResponse->isError()) {
    $error = $heidelpayResponse->getError();
    
    echo HEIDELPAY_PHP_PAYMENT_API_URL . HEIDELPAY_PHP_PAYMENT_API_FOLDER . 'HeidelpayError.php?' .
        implode('&',
        [
            'errorMessage=' . urlencode(htmlspecialchars($error['message'])),
            'shortId=' . urlencode(htmlspecialchars($heidelpayResponse->getIdentification()->getShortId()))
        ]);
}
