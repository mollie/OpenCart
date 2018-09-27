<?php

namespace Mollie\Api\Resources;

class Method extends \Mollie\Api\Resources\BaseResource
{
    /**
     * Id of the payment method.
     *
     * @var string
     */
    public $id;
    /**
     * More legible description of the payment method.
     *
     * @var string
     */
    public $description;
    /**
     * The $image->size1x and $image->size2x to display the payment method logo.
     *
     * @var object
     */
    public $image;
    /**
     * The issuers available for this payment method. Only for the methods iDEAL, KBC/CBC and gift cards.
     * Will only be filled when explicitly requested using the query string `include` parameter.
     *
     * @var array|object[]
     */
    public $issuers;
    /**
     * @var object[]
     */
    public $_links;
    /**
     * Get the issuer value objects
     *
     * @return IssuerCollection
     */
    public function issuers()
    {
        $issuers = new \Mollie\Api\Resources\IssuerCollection(\count($this->issuers), null);
        foreach ($this->issuers as $issuer) {
            $issuers->append(\Mollie\Api\Resources\ResourceFactory::createFromApiResult($issuer, new \Mollie\Api\Resources\Issuer($this->client)));
        }
        return $issuers;
    }
}
