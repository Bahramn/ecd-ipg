<?php

namespace Bahramn\EcdIpg\Gateways\Ecd\DTOs;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use phpDocumentor\Reflection\Types\Null_;

/**
 * @package Bahramn\EcdIpg\Gateways\Ecd\DTOs
 */
class EcdPaymentCallbackRequestData
{
    private int $state;
    private int $amount;
    private ?string $errorCode = null;
    private ?string $errorDescription = null;
    private ?string $referenceNumber;
    private ?string $trackingNumber;
    private string $buyId;
    private string $token;
    private string $transactionUuid;


    /**
     * @param Request $request
     * @return static
     * @throws ValidationException|BindingResolutionException
     */
    public static function createFromRequest(Request $request): self
    {
        $validator = app()->make(Factory::class);

        $validator->make($request->all(), [
            'State' => ['required', 'numeric'],
            'ErrorCode' => ['present', 'numeric', 'nullable'],
            'ErrorDescription' => ['present', 'string', 'nullable'],
            'Amount' => ['required', 'string'],
            'ReferenceNumber' => ['required', 'numeric'],
            'TrackingNumber' => ['required', 'numeric'],
            'BuyID' => ['required', 'string'],
            'Token' => ['required', 'string'],
        ])->validate();

        $instance  = new static;
        $instance->state = $request->input('State');
        $instance->errorCode = $request->input('ErrorCode');
        $instance->errorDescription = $request->input('ErrorDescription');
        $instance->amount = $request->input('Amount');
        $instance->referenceNumber = $request->input('ReferenceNumber');
        $instance->trackingNumber = $request->input('TrackingNumber');
        $instance->buyId = $request->input('BuyID');
        $instance->token = $request->input('Token');
        $instance->transactionUuid = $request->input('transaction_id');

        return $instance;
    }

    public function isSucceed(): bool
    {
        return $this->state == 1;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getPaymentUuid(): string
    {
        return $this->buyId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getRrn(): ?string
    {
        return $this->referenceNumber;
    }

    public function getStan(): ?string
    {
        return $this->trackingNumber;
    }

    public function getMessage(): ?string
    {
        return $this->errorDescription;
    }
}
