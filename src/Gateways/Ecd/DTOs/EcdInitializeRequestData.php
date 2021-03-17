<?php

namespace Bahramn\EcdIpg\Gateways\Ecd\DTOs;

use Bahramn\EcdIpg\DTOs\PaymentInitData;
use Carbon\Carbon;

/**
 * @package Bahramn\EcdIpg\Gateways\Ecd\DTOs
 */
class EcdInitializeRequestData
{
    private string $date;
    private string $time;
    private array $config;
    private PaymentInitData $initPaymentData;


    public function setInitPaymentData(PaymentInitData $initPaymentData): self
    {
        $this->initPaymentData = $initPaymentData;
        return $this;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    public function make(): self
    {
        $now = Carbon::now();
        $this->date = $now->format('Y/m/d');
        $this->time = $now->format('H:d');

        return $this;
    }

    public function getInitializeRequestBody(): array
    {
        return [
            'BuyID' => $this->initPaymentData->getUuid(),
            'TerminalNumber' => $this->getTerminalId(),
            'Amount' => $this->initPaymentData->getAmount(),
            'Date' => $this->date,
            'Time' => $this->time,
            'RedirectURL' => $this->getCallbackUrl(),
            'Language' => $this->getLocale(),
            'CheckSum' => $this->makeCheckSum(),
            'NationalCode' => $this->initPaymentData->getNid(),
            'Mobile' => $this->initPaymentData->getMobile(),
            'AdditionalData' => $this->initPaymentData->getDescription()
        ];
    }

    public function getPaymentUuid(): string
    {
        return $this->initPaymentData->getUuid();
    }

    private function getEcdKey(): string
    {
        return $this->config['key'];
    }

    private function getLocale(): string
    {
        return $this->config['locale'] ?? app()->getLocale();
    }

    private function getCallbackUrl(): string
    {
        return route('payment.callback', [
            'gateway' => $this->config['name'],
            'transaction_id' => $this->initPaymentData->getUuid()
        ]);
    }

    private function getTerminalId(): string
    {
        return $this->config['terminal_id'];
    }

    private function makeCheckSum(): string
    {
        return sha1(
            $this->getTerminalId() .
            $this->initPaymentData->getUuid() .
            $this->initPaymentData->getAmount() .
            $this->date .
            $this->time .
            $this->getCallbackUrl() .
            $this->getEcdKey()
        );
    }
}
