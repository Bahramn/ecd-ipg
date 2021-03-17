<?php

namespace Bahramn\EcdIpg\DTOs;

/**
 * @package Bahramn\EcdIpg\DTOs
 */
class HTTPRequestData
{
    public string $method;
    public string $uri;
    public array $body = [];

    /**
     * @param string $method
     * @return HTTPRequestData
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param string $uri
     * @return HTTPRequestData
     */
    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @param array $body
     * @return HTTPRequestData
     */
    public function setBody(array $body): self
    {
        $this->body = $body;

        return $this;
    }
}
