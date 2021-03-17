<?php

namespace Bahramn\EcdIpg\Gateways\Ecd\DTOs;

/**
 * @package Bahramn\EcdIpg\Gateways\Ecd\DTOs
 */
class EcdTransactionsParamsData
{
    public ?string $token = null;
    public ?string $paymentUuid = null;
    public ?string $rrn = null;
    public ?int $status = null;
    public ?string $date = null;
    public ?string $code = null;

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function setPaymentUuid(?string $paymentUuid): self
    {
        $this->paymentUuid = $paymentUuid;
        return $this;
    }

    public function setRrn(?string $rrn): self
    {
        $this->rrn = $rrn;
        return $this;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getParams(): array
    {
        $params = [
          'TerminalNumber' => config('ecd-ipg.gateways.ecd.terminal_id'),
          'Key' => config('ecd-ipg.gateways.ecd.key'),
        ];
        $mappedParams = [
          'Token'  => 'token',
          'Status'  => 'status',
          'Date'  => 'date',
          'ResponseCode'  => 'code',
          'BuyID'  => 'paymentUuid',
          'ReferenceNumber'  => 'rrn',
        ];
        foreach ($mappedParams as $key => $attribute) {
            if (!is_null($this->$attribute)) {
                $params += [$key => $this->$attribute];
            }
        }

        return $params;
    }

}
