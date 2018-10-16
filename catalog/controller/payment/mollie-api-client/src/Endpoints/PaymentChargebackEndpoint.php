<?php

namespace Mollie\Api\Endpoints;

use Mollie\Api\Resources\Chargeback;
use Mollie\Api\Resources\ChargebackCollection;
use Mollie\Api\Resources\Payment;
class PaymentChargebackEndpoint extends \Mollie\Api\Endpoints\EndpointAbstract
{
    protected $resourcePath = "payments_chargebacks";
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return Chargeback
     */
    protected function getResourceObject()
    {
        return new \Mollie\Api\Resources\Chargeback($this->client);
    }
    /**
     * Get the collection object that is used by this API endpoint. Every API endpoint uses one type of collection object.
     *
     * @param int $count
     * @param object[] $_links
     *
     * @return ChargebackCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new \Mollie\Api\Resources\ChargebackCollection($this->client, $count, $_links);
    }
    /**
     * @param Payment $payment
     * @param string $chargebackId
     * @param array $parameters
     *
     * @return Chargeback
     */
    public function getFor(\Mollie\Api\Resources\Payment $payment, $chargebackId, array $parameters = [])
    {
        $this->parentId = $payment->id;
        return parent::rest_read($chargebackId, $parameters);
    }
}
