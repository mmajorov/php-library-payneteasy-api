<?php
namespace PaynetEasy\PaynetEasyApi\Query;

use PaynetEasy\PaynetEasyApi\Utils\Validator;

use PaynetEasy\PaynetEasyApi\PaymentData\PaymentTransaction;
use PaynetEasy\PaynetEasyApi\Transport\Response;

/**
 * @see http://wiki.payneteasy.com/index.php/PnE:Sale_Transactions#Payment_status
 */
class StatusQuery extends AbstractQuery
{
    /**
     * {@inheritdoc}
     */
    static protected $requestFieldsDefinition = array
    (
        // mandatory
        array('client_orderid',     'payment.clientPaymentId',          true,    Validator::ID),
        array('orderid',            'payment.paynetPaymentId',          true,    Validator::ID),
        array('login',              'queryConfig.login',                true,    Validator::MEDIUM_STRING)
    );

    /**
     * {@inheritdoc}
     */
    static protected $signatureDefinition = array
    (
        'queryConfig.login',
        'payment.clientPaymentId',
        'payment.paynetPaymentId',
        'queryConfig.signingKey'
    );

    /**
     * {@inheritdoc}
     */
    static protected $responseFieldsDefinition = array
    (
        'type',
        'status',
        'paynet-order-id',
        'merchant-order-id',
        'serial-number'
    );

    /**
     * {@inheritdoc}
     */
    static protected $successResponseType = 'status-response';

    /**
     * {@inheritdoc}
     */
    protected function updatePaymentTransactionOnSuccess(PaymentTransaction $paymentTransaction, Response $response)
    {
        parent::updatePaymentTransactionOnSuccess($paymentTransaction, $response);

        if ($response->hasHtml())
        {
            $response->setNeededAction(Response::NEEDED_SHOW_HTML);
        }
        elseif ($response->isProcessing())
        {
            $response->setNeededAction(Response::NEEDED_STATUS_UPDATE);
        }
    }
}