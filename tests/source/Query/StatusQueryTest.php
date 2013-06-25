<?php

namespace PaynetEasy\Paynet\Query;

use PaynetEasy\Paynet\OrderData\Order;
use PaynetEasy\Paynet\Transport\Response;

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
                self::CLIENT_ORDER_ID .
                self::PAYNET_ORDER_ID .
                self::SIGN_KEY
            )
        ));
    }

    /**
     * @dataProvider testProcessRedirectProvider
     */
    public function testProcessRedirect(array $response)
    {
        $order = $this->getOrder();

        $this->object->processResponse($order, new Response($response));

        $this->assertOrderStates($order, Order::STATE_REDIRECT, Order::STATUS_PROCESSING);
        $this->assertFalse($order->hasErrors());
    }

    public function testProcessRedirectProvider()
    {
        return array(array(
        // 3D redirect
        array
        (
            'type'              => 'status-response',
            'status'            => 'processing',
            'html'              => '<HTML>',
            'paynet-order-id'   =>  self::PAYNET_ORDER_ID,
            'merchant-order-id' =>  self::CLIENT_ORDER_ID,
            'serial-number'     =>  md5(time())
        ),
        // URL redirect
        array
        (
            'type'              => 'status-response',
            'status'            => 'processing',
            'redirect-url'      => 'http://testdomain.com/',
            'paynet-order-id'   =>  self::PAYNET_ORDER_ID,
            'merchant-order-id' =>  self::CLIENT_ORDER_ID,
            'serial-number'     =>  md5(time())
        )));
    }

    /**
     * @dataProvider testProcessResponseApprovedProvider
     */
    public function testProcessResponseApproved(array $response)
    {
        $order = $this->getOrder();

        $this->object->processResponse($order, new Response($response));

        $this->assertOrderStates($order, Order::STATE_END, Order::STATUS_APPROVED);
        $this->assertFalse($order->hasErrors());
    }

    public function testProcessResponseApprovedProvider()
    {
        return array(array(array
        (
            'type'              => 'status-response',
            'status'            => 'approved',
            'paynet-order-id'   =>  self::PAYNET_ORDER_ID,
            'merchant-order-id' =>  self::CLIENT_ORDER_ID,
            'serial-number'     =>  md5(time())
        )));
    }

    /**
     * @dataProvider testProcessResponseDeclinedProvider
     */
    public function testProcessResponseDeclined(array $response)
    {
        $order = $this->getOrder();

        $this->object->processResponse($order, new Response($response));

        $this->assertOrderStates($order, Order::STATE_END, Order::STATUS_DECLINED);
        $this->assertFalse($order->hasErrors());
    }

    public function testProcessResponseDeclinedProvider()
    {
        return array(array(array
        (
            'type'              => 'status-response',
            'status'            => 'declined',
            'paynet-order-id'   =>  self::PAYNET_ORDER_ID,
            'merchant-order-id' =>  self::CLIENT_ORDER_ID,
            'serial-number'     =>  md5(time()),
            'error-message'     => 'test error message',
            'error-code'        =>  578
        )));
    }

    public function testProcessResponseFilteredProvider()
    {
        return array(array(array
        (
            'type'              => 'status-response',
            'status'            => 'filtered',
            'paynet-order-id'   =>  self::PAYNET_ORDER_ID,
            'merchant-order-id' =>  self::CLIENT_ORDER_ID,
            'serial-number'     =>  md5(time()),
            'error-message'     => 'test filtered message',
            'error-code'        =>  8876
        )));
    }

    public function testProcessResponseProcessingProvider()
    {
        return array(array(array
        (
            'type'              => 'status-response',
            'status'            => 'processing',
            'paynet-order-id'   =>  self::PAYNET_ORDER_ID,
            'merchant-order-id' =>  self::CLIENT_ORDER_ID,
            'serial-number'     =>  md5(time())
        )));
    }

    public function testProcessResponseErrorProvider()
    {
        return array(array(
        // Payment error after check
        array
        (
            'type'              => 'status-response',
            'status'            => 'error',
            'paynet-order-id'   =>  self::PAYNET_ORDER_ID,
            'merchant-order-id' =>  self::CLIENT_ORDER_ID,
            'serial-number'     =>  md5(time()),
            'error-message'     => 'status error message',
            'error-code'        =>  2
        ),
        // Validation error
        array
        (
            'type'              => 'validation-error',
            'error-message'     => 'validation error message',
            'error-code'        =>  1
        ),
        // Immediate payment error
        array
        (
            'type'              => 'error',
            'error-message'     => 'immediate error message',
            'error-code'        =>  1
        )));
    }

    /**
     * {@inheritdoc}
     */
    protected function getOrder()
    {
        return new Order(array
        (
            'client_orderid'        => self::CLIENT_ORDER_ID,
            'paynet_order_id'       => self::PAYNET_ORDER_ID
        ));
    }
}
