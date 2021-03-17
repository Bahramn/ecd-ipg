<?php

namespace Bahramn\EcdIpg\Gateways\Ecd\DTOs;

use Bahramn\EcdIpg\Exceptions\InvalidApiResponseException;
use Illuminate\Support\Facades\Lang;

/**
 * @package Bahramn\EcdIpg\Gateways\Ecd\DTOs
 */
class EcdConfirmResponseData
{
    private int $state;
    private ?string $res;
    private ?string $errorDescription;
    private ?string $errorCode;

    /**
     * @param string $rawResponse
     * @return static
     * @throws InvalidApiResponseException
     */
    public static function createFromResponse(string $rawResponse): self
    {
        $response = json_decode($rawResponse, true) ?? [];

        if (isset($response['Res'], $response['State'])) {
            $instance = new static;
            $instance->res = $response['Res'];
            $instance->state = $response['State'];
            $instance->errorCode = $response['ErrorCode'];
            $instance->errorDescription = $response['ErrorDescription'];

            return $instance;
        }
        throw new InvalidApiResponseException('ECD-Gateway');
    }

    public function isConfirmed(): bool
    {
        return $this->state == 1;
    }

    public function getMessage(): string
    {
        return $this->isConfirmed() ?
            Lang::get('ecd-ipg::messages.success') :
            $this->getErrorMessage();
    }

    private function getErrorMessage(): string
    {
        $messageKey = 'ecd-ipg::messages.ecd_error_codes.' . $this->errorCode;

        return Lang::has($messageKey) ?
            Lang::get($messageKey) :
            $this->errorDescription ?? '';
    }
}
