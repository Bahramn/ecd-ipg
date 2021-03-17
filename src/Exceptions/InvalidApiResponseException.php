<?php

namespace Bahramn\EcdIpg\Exceptions;

use Bahramn\EcdIpg\DTOs\HTTPRequestData;
use Illuminate\Contracts\Support\Arrayable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @package Bahramn\EcdIpg\Exceptions
 */
class InvalidApiResponseException extends \Exception implements Arrayable
{
    private ?RequestInterface $request;
    private ?ResponseInterface $response;
    private ?string $guzzleMessage;
    private ?HTTPRequestData $httpRequestData;

    /**
     * InvalidApiResponseException constructor.
     *
     * @param string $apiName
     * @param RequestInterface|null $request
     * @param ResponseInterface|null $response
     * @param string|null $guzzleMessage
     * @param HTTPRequestData|null $httpRequestData
     */
    public function __construct(
        string $apiName,
        ?RequestInterface $request = null,
        ?ResponseInterface $response = null,
        ?string $guzzleMessage = null,
        ?HTTPRequestData $httpRequestData = null
    ) {
        $message = 'Invalid response received from ' . $apiName . ' API.';
        parent::__construct($message);

        $this->request = $request;
        $this->response = $response;
        $this->guzzleMessage = $guzzleMessage;
        $this->httpRequestData = $httpRequestData;
    }

    public function toArray(): array
    {
        $result = [];

        if (!is_null($this->request)) {
            $this->request->getBody()->rewind();
            $result['request'] = [
                'method' => $this->request->getMethod(),
                'url' => $this->request->getUri()->__toString(),
                'headers' => $this->request->getHeaders(),
                'body' => $this->request->getBody()->getContents(),
            ];
        } elseif (!is_null($this->httpRequestData)) {
            $result['request'] = [
                'method' => $this->httpRequestData->method,
                'url' => $this->httpRequestData->uri,
                'body' => $this->httpRequestData->body,
            ];
        }

        if (!is_null($this->response)) {
            $this->response->getBody()->rewind();
            $result['response'] = [
                'status' => $this->response->getStatusCode(),
                'headers' => $this->response->getHeaders(),
                'body' => $this->response->getBody()->getContents(),
            ];
        }

        if (!is_null($this->guzzleMessage)) {
            $result['guzzleFailure'] = $this->guzzleMessage;
        }

        return $result;
    }
}
