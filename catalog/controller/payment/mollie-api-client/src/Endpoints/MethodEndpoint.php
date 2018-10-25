<?php

namespace Mollie\Api\Endpoints;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Method;
use Mollie\Api\Resources\MethodCollection;
class MethodEndpoint extends \Mollie\Api\Endpoints\EndpointAbstract
{
    protected $resourcePath = "methods";
    /**
     * @return Method
     */
    protected function getResourceObject()
    {
        return new \Mollie\Api\Resources\Method($this->client);
    }
    /**
     * Get the collection object that is used by this API endpoint. Every API endpoint uses one type of collection object.
     *
     * @param int $count
     * @param object[] $_links
     *
     * @return MethodCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new \Mollie\Api\Resources\MethodCollection($count, $_links);
    }
    /**
     * Retrieve a payment method from Mollie.
     *
     * Will throw a ApiException if the method id is invalid or the resource cannot be found.
     *
     * @param string $methodId
     * @param array $parameters
     * @return Method
     * @throws ApiException
     */
    public function get($methodId, array $parameters = [])
    {
        if (empty($methodId)) {
            throw new \Mollie\Api\Exceptions\ApiException("Method ID is empty.");
        }
        return parent::rest_read($methodId, $parameters);
    }
    /**
     * Retrieve all methods.
     *
     * @param array $parameters
     *
     * @return MethodCollection
     * @throws ApiException
     */
    public function all(array $parameters = [])
    {
        return parent::rest_list(null, null, $parameters);
    }
}
