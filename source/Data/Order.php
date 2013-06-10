<?php

namespace PaynetEasy\Paynet\Data;

use \PaynetEasy\Paynet\Exceptions\ConfigException;

/**
 * Container for order data
 *
 */
class       Order
extends     Data
implements  OrderInterface
{
    /**
     * Order customer
     *
     * @var \PaynetEasy\Paynet\Data\CustomerInterface
     */
    protected $customer;

    /**
     * Order credit card
     *
     * @var \PaynetEasy\Paynet\Data\CreditCardInterface
     */
    protected $creditCard;

    /**
     * Order recurrent card
     *
     * @var \PaynetEasy\Paynet\Data\RecurrentCardInterface
     */
    protected $recurrentCard;

    public function __construct($array)
    {
        if(isset($array['order_code']))
        {
            $array['client_orderid']    = $array['order_code'];
            unset($array['order_code']);
        }

        if(isset($array['paynet_order_id']))
        {
            $array['orderid']    = $array['paynet_order_id'];
            unset($array['paynet_order_id']);
        }

        if(isset($array['desc']))
        {
            $array['order_desc']        = $array['desc'];
            unset($array['desc']);
        }

        $this->properties = array
        (
            'client_orderid'            => true,
            'order_desc'                => true,
            'amount'                    => true,
            'currency'                  => true,
            'ipaddress'                 => true,
            'site_url'                  => false,
            'orderid'                   => false
        );

        $this->validate_preg = array
        (
            'client_orderid'            => '|^[\S\s]{1,128}$|i',
            'order_desc'                => '|^[\S\s]{1,128}$|i',
            'amount'                    => '|^[0-9\.]{1,11}$|i',
            'currency'                  => '|^[A-Z]{1,3}$|i',
            'ipaddress'                 => '|^[0-9\.]{1,20}$|i',
            'site_url'                  => '|^[\S\s]{1,128}$|i',
            'orderid'                   => '|^[\S\s]{1,32}$|i'
        );

        parent::__construct($array);
    }

    /**
     * Set order customer
     *
     * @param       \PaynetEasy\Paynet\Data\CustomerInterface        $customer       Order customer
     *
     * @return      self
     */
    public function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCustomer()
    {
        return is_object($this->getCustomer());
    }

    /**
     * Set order credit card
     *
     * @param       \PaynetEasy\Paynet\Data\CreditCardInterface     $creditCard     Order credit card
     *
     * @return      self
     */
    public function setCreditCard(CreditCardInterface $creditCard)
    {
        $this->creditCard = $creditCard;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCreditCard()
    {
        return is_object($this->getCreditCard());
    }

    /**
     * Set order recurrent card
     *
     * @param       \PaynetEasy\Paynet\Data\RecurrentCardInterface  $recurrentCard  Order recurrent card
     *
     * @return      self
     */
    public function setRecurrentCard(RecurrentCardInterface $recurrentCard)
    {
        $this->recurrentCard = $recurrentCard;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecurrentCard()
    {
        return $this->recurrentCard;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRecurrentCard()
    {
        return is_object($this->getRecurrentCard());
    }

    public function getOrderCode()
    {
        return $this->getValue('client_orderid');
    }

    public function getOrderId()
    {
        return $this->getOrderCode();
    }

    public function getPaynetOrderId()
    {
        return $this->getValue('orderid');
    }

    public function setPaynetOrderId($paynet_order_id)
    {
        $this->offsetSet('orderid', $paynet_order_id);
    }

    public function getAmount()
    {
        return $this->getValue('amount');
    }

    /**
     * Return amount in cents (use for control code)
     * @return type
     */
    public function getAmountInCents()
    {
        $amount         = (float)$this->getValue('amount');
        $amount         = explode('.', $amount);
        if(empty($amount[1]))
        {
            $amount[1]  = '00';
        }
        elseif(strlen($amount[1]) < 2)
        {
            $amount[1]  .= '0';
        }

        if(empty($amount[0]))
        {
            $amount[0]  = '';
        }

        return          $amount[0].$amount[1];
    }

    public function getCurrency()
    {
        return $this->getValue('currency');
    }

    public function getDesc()
    {
        return $this->getValue('order_desc');
    }

    public function getContextData()
    {
        return array
        (
            'client_orderid'    => $this->getOrderCode(),
            'orderid'           => $this->getPaynetOrderId()
        );
    }

    public function validateShort()
    {
        if(!$this->getPaynetOrderId())
        {
            throw new ConfigException('order.paynet_order_id undefined');
        }

        if(!$this->getOrderCode())
        {
            throw new ConfigException('order.order_code undefined');
        }
    }
}