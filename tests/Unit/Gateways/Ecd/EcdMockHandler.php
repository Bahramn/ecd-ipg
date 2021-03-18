<?php

namespace Bahramn\EcdIpg\Tests\Unit\Gateways\Ecd;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class EcdMockHandler
{
    public function __invoke(RequestInterface $request, array $options): FulfilledPromise
    {
        $requestBody = json_decode((string) $request->getBody(), true);
        $path = $request->getUri()->getPath();

        switch ($path) {
            case '/ipg_ecd/PayRequest':
                [$statusCode, $responseBody] = $this->getProperInitialResponse($requestBody);
                break;
            case '/ipg_ecd/PayConfirmation':
                [$statusCode, $responseBody] = $this->getProperConfirmResponse($requestBody);
                break;
            case '/ipg_ecd/PayReverse':
                [$statusCode, $responseBody] = $this->getProperReverseResponse($requestBody);
                break;
            case '/ipg_ecd/transactions':
                [$statusCode, $responseBody] = $this->getProperTransactionsResponse();
                break;
            default:
                $statusCode = 404;
                $responseBody = null;
        }

        return new FulfilledPromise(
            new Response($statusCode, ['Content-Type' => 'application/json'], $responseBody)
        );
    }

    private function getProperInitialResponse(array $requestBody): array
    {
        $fileName = 'initial-success';
        if ($requestBody['AdditionalData'] == 'error') {
            $fileName = 'initial-error';
        }
        if ($requestBody['AdditionalData'] == 'invalid') {
            $fileName = 'invalid-response';
        }

        return [200, $this->loadJsonFile($fileName)];
    }

    private function getProperConfirmResponse($requestBody): array
    {
        $fileName = 'confirm-success';
        if ($requestBody['Token'] == 'error') {
            $fileName = 'confirm-error';
        }
        if ($requestBody['Token'] == 'invalid') {
            $fileName = 'invalid-response';
        }

        return [200, $this->loadJsonFile($fileName)];
    }

    private function getProperReverseResponse($requestBody): array
    {
        $fileName = 'reverse-success';
        if ($requestBody['Token'] == 'error') {
            $fileName = 'reverse-error';
        }
        if ($requestBody['Token'] == 'invalid') {
            $fileName = 'invalid-response';
        }

        return [200, $this->loadJsonFile($fileName)];
    }

    private function getProperTransactionsResponse(): array
    {
        return [200, $this->loadJsonFile('transactions-success')];
    }

    private function loadJsonFile(string $fileName): string
    {
        return file_get_contents(__DIR__ . '/responses/' . $fileName . '.json');
    }
}
