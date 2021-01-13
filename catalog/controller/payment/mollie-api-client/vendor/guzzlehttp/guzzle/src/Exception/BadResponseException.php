<?php

namespace _PhpScoper5f491826ce6ce\GuzzleHttp\Exception;

use _PhpScoper5f491826ce6ce\Psr\Http\Message\RequestInterface;
use _PhpScoper5f491826ce6ce\Psr\Http\Message\ResponseInterface;
/**
 * Exception when an HTTP error occurs (4xx or 5xx error)
 */
class BadResponseException extends \_PhpScoper5f491826ce6ce\GuzzleHttp\Exception\RequestException
{
    public function __construct($message, \_PhpScoper5f491826ce6ce\Psr\Http\Message\RequestInterface $request, \_PhpScoper5f491826ce6ce\Psr\Http\Message\ResponseInterface $response = null, \Exception $previous = null, array $handlerContext = [])
    {
        if (null === $response) {
            @\trigger_error('Instantiating the ' . __CLASS__ . ' class without a Response is deprecated since version 6.3 and will be removed in 7.0.', \E_USER_DEPRECATED);
        }
        parent::__construct($message, $request, $response, $previous, $handlerContext);
    }
}
