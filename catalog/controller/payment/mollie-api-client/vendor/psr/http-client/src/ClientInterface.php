<?php

namespace _PhpScoper5f227a5b09c87\Psr\Http\Client;

use _PhpScoper5f227a5b09c87\Psr\Http\Message\RequestInterface;
use _PhpScoper5f227a5b09c87\Psr\Http\Message\ResponseInterface;
interface ClientInterface
{
    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest(\_PhpScoper5f227a5b09c87\Psr\Http\Message\RequestInterface $request) : \_PhpScoper5f227a5b09c87\Psr\Http\Message\ResponseInterface;
}
