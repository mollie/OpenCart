<?php

namespace Mollie\Api\Endpoints;

use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\PaymentCollection;
class CustomerPaymentsEndpoint extends \Mollie\Api\Endpoints\EndpointAbstract
{
    protected $resourcePath = "customers_payments";
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return Payment
     */
    protected function getResourceObject()
    {
        return new \Mollie\Api\Resources\Payment($this->client);
    }
    /**
     * Get the collection object that is used by this API endpoint. Every API endpoint uses one type of collection object.
     *
     * @param int $count
     * @param object[] $_links
     *
     * @return PaymentCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new \Mollie\Api\Resources\PaymentCollection($this->client, $count, $_links);
    }
    /**
     * Create a subscription for a Customer
     *
     * @param Customer $customer
     * @param array $options
     * @param array $filters
     *
     * @return Payment
     */
    public function createFor(\Mollie\Api\Resources\Customer $customer, array $options = [], array $filters = [])
    {
        $this->parentId = $customer->id;
        return parent::rest_create($options, $filters);
    }
    /**
     * @param Customer $customer
     * @param string $from The first resource ID you want to include in your list.
     * @param int $limit
     * @param array $parameters
     *
     * @return PaymentCollection
     */
    public function listFor(\Mollie\Api\Resources\Customer $customer, $from = null, $limit = null, array $parameters = [])
    {
        $this->parentId = $customer->id;
        return parent::rest_list($from, $limit, $parameters);
    }
}
