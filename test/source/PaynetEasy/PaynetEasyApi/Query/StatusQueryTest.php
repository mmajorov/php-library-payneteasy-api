<?php

namespace PaynetEasy\PaynetEasyApi\Query;

use PaynetEasy\PaynetEasyApi\PaymentData\Payment;
use PaynetEasy\PaynetEasyApi\Transport\Response;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-06-11 at 18:12:43.
 */
class StatusQueryTest extends QueryTestPrototype
{
    /**
     * @var StatusQuery
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new StatusQuery($this->getConfig());
    }

    public function testCreateRequestProvider()
    {
        return array(array
        (
            sha1
            (
                self::LOGIN .
                self::CLIENT_PAYMENT_ID .
                self::PAYNET_PAYMENT_ID .
                self::SIGN_KEY
            )
        ));
    }

    /**
     * @dataProvider testProcessRedirectProvider
     */
    public function testProcessRedirect(array $response)
    {
        $payment = $this->getPayment();

        $this->object->processResponse($payment, new Response($response));

        $this->assertPaymentStates($payment, Payment::STAGE_REDIRECTED, Payment::STATUS_PROCESSING);
        $this->assertFalse($payment->hasErrors());
    }

    public function testProcessRedirectProvider()
    {
        return array(
        // 3D redirect
        array(array
        (
            'type'              => 'status-response',
            'status'            => 'processing',
            'html'              => '<HTML>',
            'paynet-order-id'   =>  self::PAYNET_PAYMENT_ID,
            'merchant-order-id' =>  self::CLIENT_PAYMENT_ID,
            'serial-number'     =>  md5(time())
        )),
        // URL redirect
        array(array
        (
            'type'              => 'status-response',
            'status'            => 'processing',
            'redirect-url'      => 'http://testdomain.com/',
            'paynet-order-id'   =>  self::PAYNET_PAYMENT_ID,
            'merchant-order-id' =>  self::CLIENT_PAYMENT_ID,
            'serial-number'     =>  md5(time())
        )));
    }

    /**
     * @dataProvider testProcessResponseApprovedProvider
     */
    public function testProcessResponseApproved(array $response)
    {
        $payment = $this->getPayment();

        $this->object->processResponse($payment, new Response($response));

        $this->assertPaymentStates($payment, Payment::STAGE_FINISHED, Payment::STATUS_APPROVED);
        $this->assertFalse($payment->hasErrors());
    }

    public function testProcessResponseApprovedProvider()
    {
        return array(array(array
        (
            'type'              => 'status-response',
            'status'            => 'approved',
            'paynet-order-id'   =>  self::PAYNET_PAYMENT_ID,
            'merchant-order-id' =>  self::CLIENT_PAYMENT_ID,
            'serial-number'     =>  md5(time())
        )));
    }

    public function testProcessResponseDeclinedProvider()
    {
        return array(
        array(array
        (
            'type'              => 'status-response',
            'status'            => 'filtered',
            'paynet-order-id'   =>  self::PAYNET_PAYMENT_ID,
            'merchant-order-id' =>  self::CLIENT_PAYMENT_ID,
            'serial-number'     =>  md5(time()),
            'error-message'     => 'test filtered message',
            'error-code'        =>  8876
        )),
        array(array
        (
            'type'              => 'status-response',
            'status'            => 'declined',
            'paynet-order-id'   =>  self::PAYNET_PAYMENT_ID,
            'merchant-order-id' =>  self::CLIENT_PAYMENT_ID,
            'serial-number'     =>  md5(time()),
            'error-message'     => 'test error message',
            'error-code'        =>  578
        )));
    }

    public function testProcessResponseProcessingProvider()
    {
        return array(array(array
        (
            'type'              => 'status-response',
            'status'            => 'processing',
            'paynet-order-id'   =>  self::PAYNET_PAYMENT_ID,
            'merchant-order-id' =>  self::CLIENT_PAYMENT_ID,
            'serial-number'     =>  md5(time())
        )));
    }

    public function testProcessResponseErrorProvider()
    {
        return array(
        // Payment error after check
        array(array
        (
            'type'              => 'status-response',
            'status'            => 'error',
            'paynet-order-id'   =>  self::PAYNET_PAYMENT_ID,
            'merchant-order-id' =>  self::CLIENT_PAYMENT_ID,
            'serial-number'     =>  md5(time()),
            'error-message'     => 'status error message',
            'error-code'        =>  2
        )),
        // Validation error
        array(array
        (
            'type'              => 'validation-error',
            'error-message'     => 'validation error message',
            'error-code'        =>  1
        )),
        // Immediate payment error
        array(array
        (
            'type'              => 'error',
            'error-message'     => 'immediate error message',
            'error-code'        =>  1
        )));
    }

    /**
     * {@inheritdoc}
     */
    protected function getPayment()
    {
        return new Payment(array
        (
            'client_payment_id'     => self::CLIENT_PAYMENT_ID,
            'paynet_payment_id'     => self::PAYNET_PAYMENT_ID
        ));
    }
}