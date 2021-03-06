<?php

namespace PaynetEasy\PaynetEasyApi\Query;

use PaynetEasy\PaynetEasyApi\Query\Prototype\PaymentQuery;
use PaynetEasy\PaynetEasyApi\Util\Validator;
use PaynetEasy\PaynetEasyApi\PaymentData\Payment;

/**
 * @see http://wiki.payneteasy.com/index.php/PnE:Transfer_Transactions
 */
class TransferByNoQuery extends PaymentQuery
{
    /**
     * {@inheritdoc}
     */
    static protected $requestFieldsDefinition = array
    (
        // mandatory
        array('client_orderid',             'payment.clientId',                             true,    Validator::ID),
        array('amount',                     'payment.amount',                               true,    Validator::AMOUNT),
        array('currency',                   'payment.currency',                             true,    Validator::CURRENCY),
        array('ipaddress',                  'payment.customer.ipAddress',                   true,    Validator::IP),
        array('destination-card-no',        'payment.creditCard.creditCardNumber',          true,    Validator::CREDIT_CARD_NUMBER),
        array('login',                      'queryConfig.login',                            true,    Validator::MEDIUM_STRING),
        // optional
        array('order_desc',                 'payment.description',                          false,   Validator::LONG_STRING),
        array('redirect_url',               'queryConfig.redirectUrl',                      false,   Validator::URL),
        array('server_callback_url',        'queryConfig.callbackUrl',                      false,   Validator::URL)
    );

    /**
     * {@inheritdoc}
     */
    static protected $signatureDefinition = array
    (
        'queryConfig.login',
        'payment.clientId',
        'payment.recurrentCardFrom.paynetId',
        'payment.recurrentCardTo.paynetId',
        'payment.amountInCents',
        'payment.currency',
        'queryConfig.signingKey'
    );

    /**
     * {@inheritdoc}
     */
    static protected $paymentStatus = Payment::STATUS_CAPTURE;
}
