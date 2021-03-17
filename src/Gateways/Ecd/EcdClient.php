<?php

namespace Bahramn\EcdIpg\Gateways\Ecd;

use Bahramn\EcdIpg\DTOs\HTTPRequestData;
use Bahramn\EcdIpg\Events\GatewayHttpRequestSent;
use Bahramn\EcdIpg\Events\GatewayHttpResponseReceived;
use Bahramn\EcdIpg\Exceptions\InvalidApiResponseException;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdConfirmResponseData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdInitializeRequestData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdInitializeResponseData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdReverseResponseData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdTransactionsParamsData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdTransactionsResponseData;
use Bahramn\EcdIpg\Traits\Loggable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Illuminate\Log\Logger;
use Psr\Http\Message\ResponseInterface;

/**
 * @package Bahramn\EcdIpg\Gateways\Ecd
 */
class EcdClient extends Client
{
    use Loggable;

    /**
     * EcdClient constructor.
     *
     * @param callable|null $handler
     */
    public function __construct($handler = null)
    {
        $options = [
            'base_uri' => $this->getBaseURL(),
            'timeout' => $this->getTimeout(),
            'verify' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        if (!is_null($handler)) {
            $options['handler'] = HandlerStack::create($handler);
        }

        parent::__construct($options);
    }


    /**
     * @param EcdInitializeRequestData $data
     * @return EcdInitializeResponseData
     * @throws InvalidApiResponseException
     */
    public function initialPayment(EcdInitializeRequestData $data): EcdInitializeResponseData
    {
        $httpRequestData = (new HTTPRequestData)
            ->setMethod('POST')
            ->setUri('PayRequest')
            ->setBody($data->getInitializeRequestBody());

        try {
            $this->dispatchSendingRequest(
                $data->getPaymentUuid(),
                'Sending ECD initialize request: ',
                $httpRequestData
            );
            $result = $this->request($httpRequestData->method, $httpRequestData->uri, [
                RequestOptions::JSON => $httpRequestData->body
            ]);
            $this->dispatchResponseReceived(
                $data->getPaymentUuid(),
                'Response received from ECD initialize request: ',
                $result
            );
            $rawResponse = $result->getBody()->getContents();

            return EcdInitializeResponseData::createFromResponse($rawResponse);
        } catch (RequestException $exception) {
            $this->throwInvalidApiResponseException($exception, $httpRequestData);
        } catch (\Exception|GuzzleException $exception) {
            $this->throwInvalidApiResponseException(null, $httpRequestData);
        }
    }

    /**
     * @param string $token
     * @param string $paymentUuid
     * @return EcdConfirmResponseData
     * @throws InvalidApiResponseException
     */
    public function confirm(string $token, string $paymentUuid): EcdConfirmResponseData
    {
        $httpRequestData = (new HTTPRequestData)
            ->setMethod('POST')
            ->setUri('PayConfirmation')
            ->setBody(['Token' => $token]);

        try {
            $this->dispatchSendingRequest($paymentUuid, 'Sending ECD confirm request: ', $httpRequestData);
            $result = $this->request($httpRequestData->method, $httpRequestData->uri, [
                RequestOptions::JSON => $httpRequestData->body
            ]);
            $this->dispatchResponseReceived($paymentUuid, 'Response received from ECD confirm request: ', $result);
            $rawResponse = $result->getBody()->getContents();

            return EcdConfirmResponseData::createFromResponse($rawResponse);
        } catch (RequestException $exception) {
            $this->throwInvalidApiResponseException($exception, $httpRequestData);
        } catch (\Exception|GuzzleException $exception) {
            $this->throwInvalidApiResponseException(null, $httpRequestData);
        }
    }

    /**
     * @param string $token
     * @param string $paymentUuid
     * @return EcdReverseResponseData
     * @throws InvalidApiResponseException
     */
    public function reverse(string $token, string $paymentUuid): EcdReverseResponseData
    {
        $httpRequestData = (new HTTPRequestData)
            ->setMethod('POST')
            ->setUri('PayReverse')
            ->setBody(['Token' => $token]);

        try {
            $this->dispatchSendingRequest($paymentUuid, 'Sending ECD reverse request: ', $httpRequestData);
            $result = $this->request($httpRequestData->method, $httpRequestData->uri, [
                RequestOptions::JSON => $httpRequestData->body
            ]);
            $this->dispatchResponseReceived($paymentUuid, 'Response received from ECD reverse request: ', $result);
            $rawResponse = $result->getBody()->getContents();

            return EcdReverseResponseData::createFromResponse($rawResponse);
        } catch (RequestException $exception) {
            $this->throwInvalidApiResponseException($exception, $httpRequestData);
        } catch (\Exception|GuzzleException $exception) {
            $this->throwInvalidApiResponseException(null, $httpRequestData);
        }
    }

    /**
     * @param EcdTransactionsParamsData $paramsData
     * @return EcdTransactionsResponseData
     * @throws InvalidApiResponseException
     */
    public function transactions(EcdTransactionsParamsData $paramsData): EcdTransactionsResponseData
    {
        $httpRequestData = (new HTTPRequestData)
            ->setMethod('POST')
            ->setUri('transactions')
            ->setBody($paramsData->getParams());

        try {
            $this->log('Sending ECD transactions request: ', ['requestBody' => $httpRequestData->body]);
            $result = $this->request($httpRequestData->method, $httpRequestData->uri, [
                RequestOptions::JSON => $httpRequestData->body
            ]);
            $rawResponse = $result->getBody()->getContents();
            $this->log('Response received from ECD transactions request: ', [
                'response' => $rawResponse
            ]);

            return EcdTransactionsResponseData::createFromResponse($rawResponse);
        } catch (RequestException $exception) {
            $this->throwInvalidApiResponseException($exception, $httpRequestData);
        } catch (\Exception|GuzzleException $exception) {
            $this->throwInvalidApiResponseException(null, $httpRequestData);
        }
    }

    private function getBaseURL(): string
    {
        return env('ECD_BASE_URL', 'https://ecd.shaparak.ir/ipg_ecd/');
    }

    private function getTimeout(): int
    {
        return env('ECD_TIMEOUT', config('ecd-ipg.default_timeout'));
    }

    /**
     * @param RequestException|null $exception
     * @param HTTPRequestData  $httpRequestData
     * @throws InvalidApiResponseException
     */
    private function throwInvalidApiResponseException(?RequestException $exception, HTTPRequestData $httpRequestData): void
    {
        if (!is_null($exception)) {
            throw new InvalidApiResponseException(
                'ECD-Gateway',
                $exception->getRequest(),
                $exception->getResponse(),
                $exception->getMessage(),
                $httpRequestData
            );
        }
        throw new InvalidApiResponseException('ECD-Gateway');
    }

    /**
     * @param string          $paymentUuid
     * @param string          $message
     * @param HTTPRequestData $httpRequestData
     * @TODO: replace logging in event listener which should queue
     */
    private function dispatchSendingRequest(string $paymentUuid, string $message, HTTPRequestData $httpRequestData): void
    {
        $this->log($message, ['requestBody' => $httpRequestData->body]);
        event(new GatewayHttpRequestSent($paymentUuid, $httpRequestData, $message));
    }

    /**
     * @param string            $paymentUuid
     * @param string            $message
     * @param ResponseInterface $result
     */
    private function dispatchResponseReceived(string $paymentUuid, string $message, ResponseInterface $result): void
    {
        $this->log($message, ['response' => $result->getBody()->getContents()]);
        $result->getBody()->rewind();
        event(new GatewayHttpResponseReceived($paymentUuid, $result, $message));
    }
}
