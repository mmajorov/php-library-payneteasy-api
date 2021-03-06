<?php

namespace PaynetEasy\PaynetEasyApi;

use PaynetEasy\PaynetEasyApi\PaymentData\PaymentTransaction;
use PaynetEasy\PaynetEasyApi\PaymentData\Payment;
use PaynetEasy\PaynetEasyApi\PaymentData\Customer;
use PaynetEasy\PaynetEasyApi\PaymentData\BillingAddress;
use PaynetEasy\PaynetEasyApi\PaymentData\CreditCard;
use PaynetEasy\PaynetEasyApi\PaymentData\QueryConfig;
use PaynetEasy\PaynetEasyApi\Transport\Request;
use PaynetEasy\PaynetEasyApi\Transport\Response;
use PaynetEasy\PaynetEasyApi\Transport\CallbackResponse;

use PaynetEasy\PaynetEasyApi\Query\FakeQuery;
use PaynetEasy\PaynetEasyApi\Query\ExceptionQuery;
use PaynetEasy\PaynetEasyApi\Transport\FakeGatewayClient;

use Exception;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-06-16 at 14:01:06.
 */
class PaymentProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PublicPaymentProcessor
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PublicPaymentProcessor;
    }

    protected function tearDown()
    {
        FakeQuery::$request             = null;
        FakeGatewayClient::$response    = null;
        ExceptionQuery::$request        = null;
    }

    /**
     * @dataProvider testExecuteQueryProvider
     */
    public function testExecuteQuery($queryName, array $responseData, $handlerName)
    {
        FakeGatewayClient::$response    = new Response($responseData);

        $handlerCalled = false;
        $handler  = function() use (&$handlerCalled)
        {
            $handlerCalled = true;
        };

        $this->object->setHandler($handlerName, $handler);
        $this->object->setGatewayClient(new FakeGatewayClient);

        $this->assertNotNull($this->object->executeQuery($queryName, $this->getPaymentTransaction()));
        $this->assertTrue($handlerCalled);
    }

    public function testExecuteQueryProvider()
    {
        return(array(
        array
        (
            'status',
            array
            (
                'status'            => 'approved',
                'type'              => 'status-response',
                'paynet-order-id'   => '_',
                'merchant-order-id' => '_',
                'serial-number'     => '_'
            ),
            PaymentProcessor::HANDLER_FINISH_PROCESSING
        ),
        array
        (
            'sale-form',
            array
            (
                'redirect-url'      => 'http://example.com',
                'type'              => 'async-form-response',
                'paynet-order-id'   => '_',
                'merchant-order-id' => '_',
                'serial-number'     => '_'
            ),
            PaymentProcessor::HANDLER_REDIRECT
        ),
        array
        (
            'status',
            array
            (
                'html'              => urlencode('<html></html>'),
                'type'              => 'status-response',
                'paynet-order-id'   => '_',
                'merchant-order-id' => '_',
                'serial-number'     => '_'
            ),
            PaymentProcessor::HANDLER_SHOW_HTML
        ),
        array
        (
            'status',
            array
            (
                'status'            => 'processing',
                'type'              => 'status-response',
                'paynet-order-id'   => '_',
                'merchant-order-id' => '_',
                'serial-number'     => '_'
            ),
            PaymentProcessor::HANDLER_STATUS_UPDATE
        ),
        array
        (
            'sale',
            array
            (
                'status'            => 'processing',
                'type'              => 'async-response',
                'paynet-order-id'   => '_',
                'merchant-order-id' => '_',
                'serial-number'     => '_'
            ),
            PaymentProcessor::HANDLER_STATUS_UPDATE
        )));
    }

    /**
     * @expectedException \Exception
     */
    public function testExecuteQueryWithoutExceptionHandler()
    {
        $this->object->executeQuery('sale', new PaymentTransaction);
    }

    public function testExecuteQueryWithExceptionOnCreateRequest()
    {
        $handlerCalled = false;
        $handler  = function() use (&$handlerCalled)
        {
            $handlerCalled = true;
        };

        $this->object->setHandler(PaymentProcessor::HANDLER_CATCH_EXCEPTION, $handler);
        $this->object->executeQuery('sale', new PaymentTransaction);

        $this->assertTrue($handlerCalled);
    }

    public function testExecuteQueryWithExceptionOnMakeRequest()
    {
        $handlerCalled = false;
        $handler  = function() use (&$handlerCalled)
        {
            $handlerCalled = true;
        };

        $this->object->setHandler(PaymentProcessor::HANDLER_CATCH_EXCEPTION, $handler);

        $this->object->executeQuery('fake', new PaymentTransaction);

        $this->assertTrue($handlerCalled);
    }

    public function testExecuteQueryWithExceptionOnProcessResponse()
    {
        $handlerCalled = false;
        $handler  = function(Exception $e) use (&$handlerCalled)
        {
            $handlerCalled = true;
        };

        ExceptionQuery::$request        = new Request;
        FakeGatewayClient::$response    = new Response;

        $this->object->setHandler(PaymentProcessor::HANDLER_CATCH_EXCEPTION, $handler);
        $this->object->setGatewayClient(new FakeGatewayClient);

        $this->object->executeQuery('exception', new PaymentTransaction);

        $this->assertTrue($handlerCalled);
    }

    public function testProcessCustomerReturn()
    {
        $callbackResponse = new CallbackResponse(array
        (
            'status'            => 'approved',
            'orderid'           => '_',
            'merchant_order'    => '_',
            'client_orderid'    => '_',
            'control'           => '2c84ae87d73fa3dc116b3203e8bb1c133eed829d'
        ));

        $paymentTransaction = $this->getPaymentTransaction();
        $paymentTransaction->setStatus(PaymentTransaction::STATUS_PROCESSING);

        $handlerCalled = false;
        $handler  = function() use (&$handlerCalled)
        {
            $handlerCalled = true;
        };

        $this->object->setHandler(PaymentProcessor::HANDLER_FINISH_PROCESSING, $handler);

        $response = $this->object->processCustomerReturn($callbackResponse, $paymentTransaction);

        $this->assertTrue($handlerCalled);
        $this->assertNotNull($response);
        $this->assertInstanceOf('PaynetEasy\PaynetEasyApi\Transport\CallbackResponse', $response);
    }

    public function testProcessPaynetEasyCallback()
    {
        $handlerCalled = false;
        $handler  = function() use (&$handlerCalled)
        {
            $handlerCalled = true;
        };

        $this->object->setHandler(PaymentProcessor::HANDLER_FINISH_PROCESSING, $handler);

        $response = $this->object->processPaynetEasyCallback(new CallbackResponse(array('type' => 'fake')), new PaymentTransaction);

        $this->assertTrue($handlerCalled);
        $this->assertNotNull($response);
        $this->assertInstanceOf('PaynetEasy\PaynetEasyApi\Transport\CallbackResponse', $response);
    }

    public function testProcessPaynetEasyCallbackOnFinishedPayment()
    {
        $handlerCalled = false;
        $handler  = function() use (&$handlerCalled)
        {
            $handlerCalled = true;
        };

        $this->object->setHandler(PaymentProcessor::HANDLER_FINISH_PROCESSING, $handler);

        $this->object->processPaynetEasyCallback(new CallbackResponse(array('type' => 'fake')), new PaymentTransaction);

        $this->assertTrue($handlerCalled);
    }

    public function testProcessPaynetEasyCallbackException()
    {
        $handlerCalled = false;
        $handler  = function() use (&$handlerCalled)
        {
            $handlerCalled = true;
        };

        $this->object->setHandler(PaymentProcessor::HANDLER_CATCH_EXCEPTION, $handler);

        $this->object->processPaynetEasyCallback(new CallbackResponse(array('type' => 'sale')), new PaymentTransaction);

        $this->assertTrue($handlerCalled);
    }

    public function testhandlers()
    {
        $this->object->setHandlers(array
        (
            PaymentProcessor::HANDLER_SAVE_CHANGES => function (){},
            PaymentProcessor::HANDLER_SHOW_HTML => function (){}
        ));

        $this->assertCount(2, $this->object->handlers);
        $this->assertTrue($this->object->hasHandler(PaymentProcessor::HANDLER_SAVE_CHANGES));
        $this->assertTrue($this->object->hasHandler(PaymentProcessor::HANDLER_SHOW_HTML));

        $this->object->removeHandler(PaymentProcessor::HANDLER_SAVE_CHANGES);

        $this->assertCount(1, $this->object->handlers);
        $this->assertFalse($this->object->hasHandler(PaymentProcessor::HANDLER_SAVE_CHANGES));
        $this->assertTrue($this->object->hasHandler(PaymentProcessor::HANDLER_SHOW_HTML));

        $this->object->removeHandlers();

        $this->assertEmpty($this->object->handlers);
    }

    public function testCallHandler()
    {
        $handlerCalled = false;
        $handler  = function() use (&$handlerCalled)
        {
            $handlerCalled = true;
        };

        $this->object->setHandler(PaymentProcessor::HANDLER_SAVE_CHANGES, $handler);
        $this->object->callHandler(PaymentProcessor::HANDLER_SAVE_CHANGES, new PaymentTransaction, new Response);

        $this->assertTrue($handlerCalled);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unknown handler name: '_'
     */
    public function testSetHandlerWithWrongName()
    {
        $this->object->setHandler('_', 'not_callable');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Handler callback must be callable
     */
    public function testSetHandlerNotCallable()
    {
        $this->object->setHandler(PaymentProcessor::HANDLER_SAVE_CHANGES, 'not_callable');
    }

    protected function getPaymentTransaction()
    {
        return new PaymentTransaction(array
        (
            'payment'             =>  new Payment(array
            (
                'client_id'             => '_',
                'paynet_id'             => '_',
                'description'           => 'This is test payment',
                'amount'                =>  99.1,
                'currency'              => 'USD',
                'customer'              =>  new Customer(array
                (
                    'first_name'            => 'Vasya',
                    'last_name'             => 'Pupkin',
                    'email'                 => 'vass.pupkin@example.com',
                    'ip_address'            => '127.0.0.1',
                    'birthday'              => '112681'
                )),
                'billing_address'       =>  new BillingAddress(array
                (
                    'country'               => 'US',
                    'state'                 => 'TX',
                    'city'                  => 'Houston',
                    'first_line'            => '2704 Colonial Drive',
                    'zip_code'              => '1235',
                    'phone'                 => '660-485-6353',
                    'cell_phone'            => '660-485-6353'
                )),
                'credit_card'           => new CreditCard(array
                (
                    'card_printed_name'     => 'Vasya Pupkin',
                    'credit_card_number'    => '4485 9408 2237 9130',
                    'expire_month'          => '12',
                    'expire_year'           => '14',
                    'cvv2'                  => '084'
                ))
            )),
            'query_config'      => new QueryConfig(array
            (
                'end_point'         => 123,
                'login'             => '_',
                'redirect_url'      => 'http://example.com',
                'signing_key'       => 'key'
            ))
        ));
    }
}

class PublicPaymentProcessor extends PaymentProcessor
{
    public $handlers = array();

    public function callHandler($handlerName)
    {
        return call_user_func_array(array('parent', 'callHandler'), func_get_args());
    }

    public function hasHandler($handlerName)
    {
        return parent::hasHandler($handlerName);
    }
}