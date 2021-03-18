<?php

namespace Bahramn\EcdIpg\Gateways\Ecd\DTOs;

use Bahramn\EcdIpg\Exceptions\InvalidApiResponseException;
use Illuminate\Support\Collection;

class EcdTransactionsResponseData
{
    private int $state;
    private ?string $errorDescription;
    private ?string $errorCode;
    private Collection $transactions;

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
            $instance->state = $response['State'];
            $instance->errorCode = $response['ErrorCode'];
            $instance->errorDescription = $response['ErrorDescription'];
            $transactions = is_array($response['Res']) ?
                array_map(fn (array $trn) => EcdTransactionData::createFromArray($trn), $response['Res']) :
                [];
            $instance->transactions = new Collection($transactions);

            return $instance;
        }
        throw new InvalidApiResponseException('ECD-Gateway');
    }

    public function isSucceed(): bool
    {
        return $this->state == 1;
    }

    public function getTransactions(): Collection
    {
        return $this->transactions;
    }
}
